<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    /**
     * Display the review form for a completed booking.
     */
    public function create(Request $request, int $bookingId): Response|RedirectResponse
    {
        $booking = Booking::with(['service', 'offering', 'provider'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->findOrFail($bookingId);

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
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'service_id' => 'required|exists:services,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $booking = Booking::where('user_id', $request->user()->id)
            ->where('id', $validated['booking_id'])
            ->where('status', 'completed')
            ->firstOrFail();

        // Check if already reviewed
        $existingReview = Review::where('booking_id', $validated['booking_id'])->first();
        if ($existingReview) {
            return back()->with('error', __('You have already reviewed this booking.'));
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'service_id' => $validated['service_id'],
            'booking_id' => $validated['booking_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'tags' => $validated['tags'] ?? [],
            'is_approved' => true, // Auto-approve for now, can add moderation later
            'reviewed_at' => now(),
        ]);

        // Update service rating stats
        $service = Service::find($validated['service_id']);
        $service->updateRatingStats();

        return redirect()->route('bookings.index')
            ->with('success', __('Thank you for your review!'));
    }

    /**
     * Get user's reviews.
     */
    public function userReviews(Request $request): Response
    {
        $reviews = Review::with(['service', 'booking'])
            ->where('user_id', $request->user()->id)
            ->orderBy('reviewed_at', 'desc')
            ->paginate(10);

        return Inertia::render('Reviews/UserReviews', [
            'reviews' => $reviews,
        ]);
    }
}
