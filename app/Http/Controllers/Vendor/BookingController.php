<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Shop;
use App\Services\BookingService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $shops = Shop::where('user_id', $user->id)->get();
        $shopIds = $shops->pluck('id');

        // Build query
        $query = Booking::whereIn('shop_id', $shopIds)
            ->with(['customer', 'shop', 'service']);

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
            $query->where(function ($search) use ($request) {
                $search->where('customer_name', 'like', '%'.$request->search.'%')
                    ->orWhere('customer_email', 'like', '%'.$request->search.'%')
                    ->orWhereHas('customer', function ($customer) use ($request) {
                        $customer->where('name', 'like', '%'.$request->search.'%')
                            ->orWhere('email', 'like', '%'.$request->search.'%');
                    });
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
        $allBookings = Booking::whereIn('shop_id', $shopIds)->with('shop')->get();

        // Calculate revenue per shop to show aggregates ONLY per shop
        $revenueByShop = $allBookings->where('status', '!=', 'cancelled')->groupBy('shop_id')->map(function ($shopBookings) {
            $shop = $shopBookings->first()->shop;
            $currency = $shop ? $shop->currency : 'CZK';
            $amount = $shopBookings->sum('total_price');

            return $shop->name.': '.Money::format($amount, $currency);
        });

        $revenueString = $revenueByShop->isEmpty() ? '0.00 CZK' : $revenueByShop->implode(' | ');

        $stats = [
            'total' => $allBookings->count(),
            'pending' => $allBookings->where('status', 'pending')->count(),
            'confirmed' => $allBookings->where('status', 'confirmed')->count(),
            'completed' => $allBookings->where('status', 'completed')->count(),
            'cancelled' => $allBookings->where('status', 'cancelled')->count(),
            'total_revenue' => $revenueString,
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

        $shops = Shop::where('user_id', $user->id)->pluck('id');

        $booking = Booking::whereIn('shop_id', $shops)
            ->with(['customer', 'shop', 'service', 'provider'])
            ->findOrFail($id);
        $this->authorize('manage', $booking);

        // Get customer booking history with this vendor
        $customerHistory = Booking::where('provider_id', $user->id)
            ->when(
                $booking->user_id,
                fn ($query) => $query->where('user_id', $booking->user_id),
                fn ($query) => $query->whereNull('user_id')
                    ->where('customer_email', $booking->customer_email),
            )
            ->where('id', '!=', $booking->id)
            ->with(['shop', 'service'])
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
    public function confirm(Request $request, int $id, BookingService $bookings): RedirectResponse
    {
        $user = $request->user();

        $shops = Shop::where('user_id', $user->id)->pluck('id');

        $booking = Booking::whereIn('shop_id', $shops)
            ->where('status', 'pending')
            ->with(['customer', 'shop', 'service', 'provider'])
            ->findOrFail($id);
        $this->authorize('manage', $booking);

        $bookings->transition($booking, BookingStatus::Confirmed);

        return back()->with('success', __('Booking confirmed successfully.'));
    }

    /**
     * Complete a confirmed booking.
     */
    public function complete(Request $request, int $id, BookingService $bookings): RedirectResponse
    {
        $user = $request->user();

        $shops = Shop::where('user_id', $user->id)->pluck('id');

        $booking = Booking::whereIn('shop_id', $shops)
            ->where('status', BookingStatus::Confirmed->value)
            ->with(['customer', 'shop', 'service', 'provider'])
            ->findOrFail($id);
        $this->authorize('manage', $booking);

        $bookings->transition($booking, BookingStatus::Completed);

        return back()->with('success', __('Booking marked as completed.'));
    }

    /**
     * Update booking status.
     */
    public function update(Request $request, int $bookingId, BookingService $bookings): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled|nullable|string|max:500',
        ]);

        $shopIds = Shop::where('user_id', $request->user()->id)->pluck('id');
        $booking = Booking::whereIn('shop_id', $shopIds)->findOrFail($bookingId);
        $this->authorize('manage', $booking);
        $bookings->transition(
            $booking,
            BookingStatus::from($validated['status']),
            $validated['cancellation_reason'] ?? null,
        );

        return back()->with('success', __('Booking status updated successfully.'));
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, int $id, BookingService $bookings): RedirectResponse
    {
        $user = $request->user();

        $shops = Shop::where('user_id', $user->id)->pluck('id');

        $booking = Booking::whereIn('shop_id', $shops)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['customer', 'shop', 'service', 'provider'])
            ->findOrFail($id);
        $this->authorize('manage', $booking);

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);
        $bookings->transition(
            $booking,
            BookingStatus::Cancelled,
            $validated['cancellation_reason'],
        );

        return back()->with('success', __('Booking cancelled successfully.'));
    }

    /**
     * Add notes to a booking.
     */
    public function addNotes(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        $shops = Shop::where('user_id', $user->id)->pluck('id');

        $booking = Booking::whereIn('shop_id', $shops)->findOrFail($id);
        $this->authorize('manage', $booking);

        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        $existingNotes = $booking->notes ?? '';
        $newNotes = $existingNotes."\n[".now()->format('Y-m-d H:i').'] '.$validated['notes'];

        $booking->update(['notes' => $newNotes]);

        return back()->with('success', __('Notes added successfully.'));
    }
}
