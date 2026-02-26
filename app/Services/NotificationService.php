<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;

class NotificationService
{
    /**
     * Create a new booking notification for the customer.
     */
    public static function bookingCreated(Booking $booking): void
    {
        // Notify customer
        Notification::create([
            'user_id' => $booking->user_id,
            'type' => 'booking',
            'title' => 'Booking Confirmed',
            'message' => "Your booking for {$booking->service->name} on {$booking->booking_date->format('M d, Y')} at {$booking->start_time} has been confirmed.",
            'data' => [
                'booking_id' => $booking->id,
                'service_id' => $booking->service_id,
                'service_name' => $booking->service->name,
            ],
            'action_url' => route('bookings.confirmation', $booking->id),
        ]);

        // Notify provider
        Notification::create([
            'user_id' => $booking->provider_id,
            'type' => 'booking',
            'title' => 'New Booking Received',
            'message' => "You have a new booking from {$booking->customer->name} for {$booking->service->name} on {$booking->booking_date->format('M d, Y')}.",
            'data' => [
                'booking_id' => $booking->id,
                'customer_name' => $booking->customer->name,
                'service_name' => $booking->service->name,
            ],
            'action_url' => route('vendor.bookings.show', $booking->id),
        ]);
    }

    /**
     * Create a booking status update notification.
     */
    public static function bookingStatusUpdated(Booking $booking, string $oldStatus): void
    {
        $statusMessages = [
            'confirmed' => 'Your booking has been confirmed by the provider.',
            'completed' => 'Your service has been marked as completed. Please leave a review!',
            'cancelled' => 'Your booking has been cancelled.',
        ];

        if (!isset($statusMessages[$booking->status])) {
            return;
        }

        // Notify customer of status change
        Notification::create([
            'user_id' => $booking->user_id,
            'type' => 'booking',
            'title' => 'Booking ' . ucfirst($booking->status),
            'message' => $statusMessages[$booking->status],
            'data' => [
                'booking_id' => $booking->id,
                'service_name' => $booking->service->name,
                'status' => $booking->status,
            ],
            'action_url' => route('bookings.index'),
        ]);

        // If cancelled, also notify provider
        if ($booking->status === 'cancelled') {
            Notification::create([
                'user_id' => $booking->provider_id,
                'type' => 'booking',
                'title' => 'Booking Cancelled',
                'message' => "A booking for {$booking->service->name} on {$booking->booking_date->format('M d, Y')} has been cancelled.",
                'data' => [
                    'booking_id' => $booking->id,
                    'service_name' => $booking->service->name,
                ],
                'action_url' => route('vendor.bookings.index'),
            ]);
        }
    }

    /**
     * Create a payment notification.
     */
    public static function paymentReceived(Booking $booking): void
    {
        // Notify customer
        Notification::create([
            'user_id' => $booking->user_id,
            'type' => 'payment',
            'title' => 'Payment Successful',
            'message' => "Your payment of $" . number_format($booking->total_price, 2) . " for {$booking->service->name} has been received.",
            'data' => [
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'service_name' => $booking->service->name,
            ],
            'action_url' => route('bookings.confirmation', $booking->id),
        ]);

        // Notify provider
        Notification::create([
            'user_id' => $booking->provider_id,
            'type' => 'payment',
            'title' => 'Payment Received',
            'message' => "You received a payment of $" . number_format($booking->total_price, 2) . " from {$booking->customer->name}.",
            'data' => [
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'customer_name' => $booking->customer->name,
            ],
            'action_url' => route('vendor.bookings.show', $booking->id),
        ]);
    }

    /**
     * Create a review notification.
     */
    public static function reviewReceived(Review $review): void
    {
        $booking = $review->booking;

        Notification::create([
            'user_id' => $booking->provider_id,
            'type' => 'review',
            'title' => 'New Review Received',
            'message' => "You received a {$review->rating}-star review from {$review->user->name} for {$booking->service->name}.",
            'data' => [
                'review_id' => $review->id,
                'booking_id' => $booking->id,
                'rating' => $review->rating,
                'customer_name' => $review->user->name,
            ],
            'action_url' => route('services.show', $booking->service->slug),
        ]);
    }

    /**
     * Create a reminder notification.
     */
    public static function sendReminder(Booking $booking, string $reminderType): void
    {
        $reminders = [
            '24h_customer' => [
                'title' => 'Upcoming Booking Tomorrow',
                'message' => "Reminder: You have a booking for {$booking->service->name} tomorrow at {$booking->start_time}.",
            ],
            '24h_provider' => [
                'title' => 'Upcoming Booking Tomorrow',
                'message' => "Reminder: You have a booking with {$booking->customer->name} tomorrow at {$booking->start_time}.",
            ],
            '1h_customer' => [
                'title' => 'Booking in 1 Hour',
                'message' => "Your {$booking->service->name} appointment is in 1 hour. Please be ready!",
            ],
            '1h_provider' => [
                'title' => 'Booking in 1 Hour',
                'message' => "Your appointment with {$booking->customer->name} is in 1 hour.",
            ],
        ];

        if (!isset($reminders[$reminderType])) {
            return;
        }

        $userId = str_contains($reminderType, 'customer') ? $booking->user_id : $booking->provider_id;

        Notification::create([
            'user_id' => $userId,
            'type' => 'reminder',
            'title' => $reminders[$reminderType]['title'],
            'message' => $reminders[$reminderType]['message'],
            'data' => [
                'booking_id' => $booking->id,
                'service_name' => $booking->service->name,
            ],
            'action_url' => str_contains($reminderType, 'customer') 
                ? route('bookings.index') 
                : route('vendor.bookings.show', $booking->id),
        ]);
    }

    /**
     * Get unread count for a user.
     */
    public static function getUnreadCount(User $user): int
    {
        return $user->notifications()->unread()->count();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public static function markAllAsRead(User $user): void
    {
        $user->notifications()->unread()->update(['read_at' => now()]);
    }
}
