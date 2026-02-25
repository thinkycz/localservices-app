<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceOffering;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Display the booking form for a service.
     */
    public function show(string $slug, Request $request): Response
    {
        $service = Service::with(['category', 'offerings'])
            ->where('slug', $slug)
            ->firstOrFail();

        $offering = null;
        if ($request->filled('offering_id')) {
            $offering = $service->offerings->firstWhere('id', (int) $request->offering_id);
        }

        return Inertia::render('Booking/Index', [
            'service' => $service,
            'offering' => $offering,
            'date' => $request->get('date'),
            'time' => $request->get('time'),
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
            'provider_id' => 'required|exists:users,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'customer_notes' => 'nullable|string|max:1000',
        ]);

        // Get the offering to calculate total price
        $offering = ServiceOffering::findOrFail($validated['service_offering_id']);
        
        // Calculate end time based on duration
        $startTime = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime = $startTime->copy()->addMinutes($offering->duration_minutes);

        // For guest bookings, we create a temporary record or require login
        // For now, we'll require authentication for booking
        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'service_id' => $validated['service_id'],
            'service_offering_id' => $validated['service_offering_id'],
            'provider_id' => $validated['provider_id'],
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
            $user->update(['name' => $validated['first_name'] . ' ' . $validated['last_name']]);
        }
        if (!$user->phone) {
            $user->update(['phone' => $validated['phone']]);
        }

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
            ->findOrFail($id);

        // Only allow cancellation of pending or confirmed bookings
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully.');
    }
}
