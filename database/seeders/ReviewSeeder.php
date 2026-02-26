<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Get completed bookings that don't have reviews yet
        $completedBookings = Booking::where('status', 'completed')
            ->whereNotIn('id', Review::pluck('booking_id'))
            ->with(['service', 'customer'])
            ->get();
        
        $tags = [
            'Professional',
            'On-time',
            'Quality Service',
            'Friendly',
            'Clean',
            'Good Value',
            'Expert',
            'Recommended',
        ];
        
        $comments = [
            'Excellent service! Very professional and completed the job quickly.',
            'Great experience. Arrived on time and did quality work.',
            'Highly recommend! Fair pricing and very knowledgeable.',
            'Good service overall. Would use again.',
            'Outstanding work! Exceeded my expectations.',
            'Very friendly and efficient. Cleaned up after the job.',
            'Professional service from start to finish.',
            'Satisfied with the results. Good communication throughout.',
            'Quick response time and quality workmanship.',
            'Reasonable price for excellent service. Will definitely recommend.',
        ];
        
        // Create reviews for about 60% of completed bookings
        $bookingsToReview = $completedBookings->shuffle()->take((int) ($completedBookings->count() * 0.6));
        
        foreach ($bookingsToReview as $booking) {
            $rating = $this->weightedRandomChoice(
                [5, 4, 3, 2, 1],
                [50, 30, 12, 5, 3] // Mostly positive reviews
            );
            
            $selectedTags = collect($tags)->random(rand(1, 4))->toArray();
            
            Review::create([
                'user_id' => $booking->user_id,
                'service_id' => $booking->service_id,
                'booking_id' => $booking->id,
                'rating' => $rating,
                'comment' => $comments[array_rand($comments)],
                'tags' => $selectedTags,
                'is_approved' => true,
                'reviewed_at' => Carbon::parse($booking->booking_date)->addDays(rand(1, 5)),
            ]);
            
            // Update service rating stats
            $service = Service::find($booking->service_id);
            $service->updateRatingStats();
        }
    }
    
    /**
     * Weighted random choice from array
     */
    private function weightedRandomChoice(array $choices, array $weights): int
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($choices as $index => $choice) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $choice;
            }
        }
        
        return $choices[0];
    }
}
