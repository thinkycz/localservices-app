<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmation;
use App\Mail\NewBookingNotification;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceOffering;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Display the booking form for a service.
     */
    public function show(string $slug, Request $request): Response
    {
        $service = Service::with(['category', 'offerings', 'owner'])
            ->where('slug', $slug)
            ->firstOrFail();

        $offering = null;
        if ($request->filled('offering_id')) {
            $offering = $service->offerings->firstWhere('id', (int) $request->offering_id);
        }

        // Convert service to array and ensure user_id is included
        $serviceArray = $service->toArray();
        $serviceArray['user_id'] = $service->user_id;

        // Get authenticated user data for prefilling form
        $authUser = $request->user();

        return Inertia::render('Booking/Index', [
            'service' => $serviceArray,
            'offering' => $offering,
            'date' => $request->get('date'),
            'time' => $request->get('time'),
            'authUser' => $authUser ? [
                'name' => $authUser->name,
                'email' => $authUser->email,
                'phone' => $authUser->phone,
            ] : null,
        ]);
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'service_offering_id' => 'required|exists:service_offerings,id',
            'provider_id' => 'nullable|exists:users,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'customer_notes' => 'nullable|string|max:1000',
        ]);

        // Get the service to determine provider_id
        $service = Service::findOrFail($validated['service_id']);
        
        // If provider_id is not provided, get it from the service's user_id
        $providerId = $validated['provider_id'] ?? $service->user_id;
        
        // If still no provider, get the first service provider user as fallback
        if (!$providerId) {
            $provider = \App\Models\User::where('is_service_provider', true)->first();
            if (!$provider) {
                return back()->with('error', 'No service provider available for this service.');
            }
            $providerId = $provider->id;
        }

        // Get the offering to calculate total price
        $offering = ServiceOffering::findOrFail($validated['service_offering_id']);
        
        // Calculate end time based on duration
        // Try to parse time in various formats (12-hour or 24-hour)
        $startTime = \Carbon\Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addMinutes($offering->duration_minutes);

        // For guest bookings, we create a temporary record or require login
        // For now, we'll require authentication for booking
        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'service_id' => $validated['service_id'],
            'service_offering_id' => $validated['service_offering_id'],
            'provider_id' => $providerId,
            'status' => 'pending',
            'booking_date' => $validated['booking_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $endTime->format('H:i'),
            'total_price' => $offering->price,
            'customer_notes' => $validated['customer_notes'] ?? null,
        ]);

        // Update user's name and phone if not set
        $user = $request->user();
        if (!$user->name) {
            $user->update(['name' => $validated['full_name']]);
        }
        if (!$user->phone) {
            $user->update(['phone' => $validated['phone']]);
        }

        // Send confirmation email to customer
        Mail::to($user->email)->send(new BookingConfirmation($booking));

        // Send notification email to vendor
        Mail::to($booking->provider->email)->send(new NewBookingNotification($booking));

        return redirect()->route('bookings.confirmation', $booking->id)
            ->with('success', 'Booking created successfully!');
    }

    /**
     * Display booking confirmation.
     */
    public function confirmation(int $id, Request $request): Response
    {
        $booking = Booking::with(['service', 'offering', 'provider'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return Inertia::render('Booking/Confirmation', [
            'booking' => $booking,
        ]);
    }

    /**
     * Display user's booking history.
     */
    public function userBookings(Request $request): Response
    {
        $bookings = Booking::with(['service', 'offering', 'provider'])
            ->where('user_id', $request->user()->id)
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        // Add has_review flag to each booking
        $bookingIds = $bookings->pluck('id');
        $reviewedBookingIds = \App\Models\Review::whereIn('booking_id', $bookingIds)
            ->pluck('booking_id')
            ->toArray();

        $bookings->through(function ($booking) use ($reviewedBookingIds) {
            $booking->has_review = in_array($booking->id, $reviewedBookingIds);
            return $booking;
        });

        return Inertia::render('Booking/UserBookings', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $booking = Booking::where('user_id', $request->user()->id)
            ->with(['customer', 'service', 'offering', 'provider'])
            ->findOrFail($id);

        // Only allow cancellation of pending or confirmed bookings
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        $oldStatus = $booking->status;

        $booking->update(['status' => 'cancelled']);

        // Send status update email to customer
        Mail::to($booking->customer->email)->send(new \App\Mail\BookingStatusUpdated($booking, $oldStatus, 'cancelled'));

        // Notify vendor of cancellation
        Mail::to($booking->provider->email)->send(new \App\Mail\BookingStatusUpdated($booking, $oldStatus, 'cancelled'));

        return back()->with('success', 'Booking cancelled successfully.');
    }
}
