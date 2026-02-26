<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Mail\BookingStatusUpdated;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Display a list of all bookings for the vendor.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get all services for this vendor
        $services = Service::where('user_id', $user->id)->get();
        $serviceIds = $services->pluck('id');

        // Build query
        $query = Booking::whereIn('service_id', $serviceIds)
            ->with(['customer', 'service', 'offering']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }

        // Search by customer name
        if ($request->filled('search')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'date_asc' => $query->orderBy('booking_date', 'asc')->orderBy('start_time', 'asc'),
            'date_desc' => $query->orderBy('booking_date', 'desc')->orderBy('start_time', 'desc'),
            default => $query->latest(),
        };

        $bookings = $query->paginate(15)->withQueryString();

        // Calculate stats
        $allBookings = Booking::whereIn('service_id', $serviceIds)->get();
        $stats = [
            'total' => $allBookings->count(),
            'pending' => $allBookings->where('status', 'pending')->count(),
            'confirmed' => $allBookings->where('status', 'confirmed')->count(),
            'completed' => $allBookings->where('status', 'completed')->count(),
            'cancelled' => $allBookings->where('status', 'cancelled')->count(),
            'total_revenue' => $allBookings->where('status', '!=', 'cancelled')->sum('total_price'),
        ];

        return Inertia::render('Vendor/Bookings/Index', [
            'bookings' => $bookings,
            'stats' => $stats,
            'filters' => $request->only(['status', 'date_from', 'date_to', 'search', 'sort']),
        ]);
    }

    /**
     * Display details of a specific booking.
     */
    public function show(Request $request, int $id): Response
    {
        $user = $request->user();

        $services = Service::where('user_id', $user->id)->pluck('id');

        $booking = Booking::whereIn('service_id', $services)
            ->with(['customer', 'service', 'offering', 'provider'])
            ->findOrFail($id);

        // Get customer booking history with this vendor
        $customerHistory = Booking::where('provider_id', $user->id)
            ->where('user_id', $booking->user_id)
            ->where('id', '!=', $booking->id)
            ->with(['service', 'offering'])
            ->orderBy('booking_date', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Vendor/Bookings/Show', [
            'booking' => $booking,
            'customerHistory' => $customerHistory,
        ]);
    }

    /**
     * Confirm a pending booking.
     */
    public function confirm(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        $services = Service::where('user_id', $user->id)->pluck('id');

        $booking = Booking::whereIn('service_id', $services)
            ->where('status', 'pending')
            ->with(['customer', 'service', 'offering', 'provider'])
            ->findOrFail($id);

        $oldStatus = $booking->status;

        $booking->update([
            'status' => 'confirmed',
            'notes' => $booking->notes . "\n[Confirmed by vendor on " . now()->format('Y-m-d H:i') . "]",
        ]);

        // Send status update email to customer
        Mail::to($booking->customer->email)->send(new BookingStatusUpdated($booking, $oldStatus, 'confirmed'));

        return back()->with('success', 'Booking confirmed successfully.');
    }

    /**
     * Complete a confirmed booking.
     */
    public function complete(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        $services = Service::where('user_id', $user->id)->pluck('id');

        $booking = Booking::whereIn('service_id', $services)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['customer', 'service', 'offering', 'provider'])
            ->findOrFail($id);

        $oldStatus = $booking->status;

        $booking->update([
            'status' => 'completed',
            'notes' => $booking->notes . "\n[Completed on " . now()->format('Y-m-d H:i') . "]",
        ]);

        // Send status update email to customer
        Mail::to($booking->customer->email)->send(new BookingStatusUpdated($booking, $oldStatus, 'completed'));

        return back()->with('success', 'Booking marked as completed.');
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        $services = Service::where('user_id', $user->id)->pluck('id');

        $booking = Booking::whereIn('service_id', $services)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['customer', 'service', 'offering', 'provider'])
            ->findOrFail($id);

        $oldStatus = $booking->status;

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $notes = $booking->notes . "\n[Cancelled by vendor on " . now()->format('Y-m-d H:i') . "]";
        if (!empty($validated['cancellation_reason'])) {
            $notes .= "\nReason: " . $validated['cancellation_reason'];
        }

        $booking->update([
            'status' => 'cancelled',
            'notes' => $notes,
        ]);

        // Send status update email to customer
        Mail::to($booking->customer->email)->send(new BookingStatusUpdated($booking, $oldStatus, 'cancelled'));

        return back()->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Add notes to a booking.
     */
    public function addNotes(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        $services = Service::where('user_id', $user->id)->pluck('id');

        $booking = Booking::whereIn('service_id', $services)->findOrFail($id);

        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        $existingNotes = $booking->notes ?? '';
        $newNotes = $existingNotes . "\n[" . now()->format('Y-m-d H:i') . "] " . $validated['notes'];

        $booking->update(['notes' => $newNotes]);

        return back()->with('success', 'Notes added successfully.');
    }

    /**
     * Get available time slots for a service on a specific date.
     */
    public function getAvailableSlots(Request $request, int $serviceId): Response
    {
        $user = $request->user();

        $service = Service::where('user_id', $user->id)->findOrFail($serviceId);

        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $date = $validated['date'];

        // Get existing bookings for this service on this date
        $existingBookings = Booking::where('service_id', $serviceId)
            ->whereDate('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['start_time', 'end_time']);

        // Generate time slots (9 AM to 6 PM, 30 min intervals)
        $slots = [];
        $start = strtotime('09:00');
        $end = strtotime('18:00');

        for ($time = $start; $time < $end; $time += 1800) { // 30 min intervals
            $slotStart = date('H:i', $time);
            $slotEnd = date('H:i', $time + 1800);

            // Check if slot is available (no overlapping bookings)
            $isAvailable = true;
            foreach ($existingBookings as $booking) {
                $bookingStart = substr($booking->start_time, 0, 5);
                $bookingEnd = substr($booking->end_time, 0, 5);

                if ($slotStart < $bookingEnd && $slotEnd > $bookingStart) {
                    $isAvailable = false;
                    break;
                }
            }

            $slots[] = [
                'time' => $slotStart,
                'available' => $isAvailable,
            ];
        }

        return response()->json([
            'service_id' => $serviceId,
            'date' => $date,
            'slots' => $slots,
        ]);
    }
}
