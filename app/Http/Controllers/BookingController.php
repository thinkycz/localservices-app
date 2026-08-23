<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Shop;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Display the booking form for a service.
     */
    public function show(string $slug, Request $request): Response
    {
        $shop = Shop::with([
            'category',
            'services' => fn ($query) => $query->where('is_available', true),
            'businessHours',
        ])
            ->where('slug', $slug)
            ->where('is_available', true)
            ->firstOrFail();

        $service = null;
        if ($request->filled('service_id')) {
            $service = $shop->services->firstWhere('id', (int) $request->service_id);
        }

        $shopArray = $shop->toArray();
        $shopArray['user_id'] = $shop->user_id;

        // Get authenticated user data for prefilling form
        $authUser = $request->user();

        return Inertia::render('Booking/Index', [
            'shop' => $shopArray,
            'service' => $service,
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
    public function store(StoreBookingRequest $request, BookingService $bookings): RedirectResponse
    {
        $result = $bookings->create($request->validated(), $request->user());

        if ($result['guest_token']) {
            return redirect()->route('guest.bookings.show', [
                'booking' => $result['booking']->id,
                'token' => $result['guest_token'],
            ])->with('success', __('Booking created successfully!'));
        }

        return redirect()->route('bookings.confirmation', $result['booking']->id)
            ->with('success', __('Booking created successfully!'));
    }

    /**
     * Display booking confirmation.
     */
    public function confirmation(int $id, Request $request): Response
    {
        $booking = Booking::with(['shop', 'service', 'provider'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);
        $this->authorize('view', $booking);

        return Inertia::render('Booking/Confirmation', [
            'booking' => $booking,
            'canCancel' => $booking->canBeCancelledByCustomer(),
        ]);
    }

    /**
     * Display user's booking history.
     */
    public function userBookings(Request $request): Response
    {
        $bookings = Booking::with(['shop', 'service', 'provider'])
            ->where('user_id', $request->user()->id)
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        // Add has_review flag to each booking
        $bookingIds = $bookings->pluck('id');
        $reviewedBookingIds = Review::whereIn('booking_id', $bookingIds)
            ->pluck('booking_id')
            ->toArray();

        $bookings->through(function ($booking) use ($reviewedBookingIds) {
            $booking->has_review = in_array($booking->id, $reviewedBookingIds);
            $booking->can_cancel = $booking->canBeCancelledByCustomer();
            $booking->is_upcoming = $booking->appointmentStartsAt()->isFuture();
            $booking->cancellation_deadline = $booking->appointmentStartsAt()
                ->subHours(24)
                ->toIso8601String();

            return $booking;
        });

        return Inertia::render('Booking/UserBookings', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, int $id, BookingService $bookings): RedirectResponse
    {
        $booking = Booking::where('user_id', $request->user()->id)
            ->with(['customer', 'shop', 'service', 'provider'])
            ->findOrFail($id);
        $this->authorize('cancel', $booking);

        $bookings->cancelByCustomer($booking);

        return back()->with('success', __('Booking cancelled successfully.'));
    }
}
