<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use App\Models\Review;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    /**
     * Display the review form for a completed booking.
     */
    public function create(Request $request, int $bookingId): Response|RedirectResponse
    {
        $booking = Booking::with(['shop', 'service', 'provider'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->findOrFail($bookingId);
        $this->authorize('review', $booking);

        // Check if already reviewed
        $existingReview = Review::where('booking_id', $bookingId)->first();
        if ($existingReview) {
            return redirect()->route('bookings.index')
                ->with('info', 'You have already reviewed this booking.');
        }

        return Inertia::render('Reviews/Create', [
            'booking' => $booking,
        ]);
    }

    /**
     * Store a new review.
     */
    public function store(StoreReviewRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $booking = Booking::with('shop')
            ->where('user_id', $request->user()->id)
            ->where('id', $validated['booking_id'])
            ->where('status', 'completed')
            ->firstOrFail();
        $this->authorize('review', $booking);

        // Check if already reviewed
        $existingReview = Review::where('booking_id', $validated['booking_id'])->first();
        if ($existingReview) {
            return back()->with('error', __('You have already reviewed this booking.'));
        }

        DB::transaction(function () use ($booking, $request, $validated): void {
            $review = Review::create([
                'user_id' => $request->user()->id,
                'shop_id' => $booking->shop_id,
                'booking_id' => $validated['booking_id'],
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'tags' => $validated['tags'] ?? [],
                'is_approved' => true,
                'reviewed_at' => now(),
            ]);

            $booking->shop->updateRatingStats();
            DB::afterCommit(fn () => NotificationService::reviewReceived($review->load(['booking.service', 'user'])));
        });

        return redirect()->route('bookings.index')
            ->with('success', __('Thank you for your review!'));
    }

    /**
     * Get user's reviews.
     */
    public function userReviews(Request $request): Response
    {
        $reviews = Review::with(['shop', 'booking'])
            ->where('user_id', $request->user()->id)
            ->orderBy('reviewed_at', 'desc')
            ->paginate(10);

        return Inertia::render('Reviews/UserReviews', [
            'reviews' => $reviews,
        ]);
    }
}
