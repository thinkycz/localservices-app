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
        if ($booking->user_id) {
            Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking',
                'title' => 'Rezervace byla přijata',
                'message' => "Rezervace služby {$booking->service->name} na {$booking->booking_date->format('d. m. Y')} v ".substr($booking->start_time, 0, 5).' čeká na potvrzení.',
                'data' => [
                    'booking_id' => $booking->id,
                    'service_id' => $booking->service_id,
                    'service_name' => $booking->service->name,
                ],
                'action_url' => route('bookings.confirmation', $booking->id),
            ]);
        }

        // Notify provider
        Notification::create([
            'user_id' => $booking->provider_id,
            'type' => 'booking',
            'title' => 'Nová rezervace',
            'message' => "{$booking->customer_display_name} rezervoval(a) službu {$booking->service->name} na {$booking->booking_date->format('d. m. Y')}.",
            'data' => [
                'booking_id' => $booking->id,
                'customer_name' => $booking->customer_display_name,
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
            'confirmed' => 'Poskytovatel potvrdil vaši rezervaci.',
            'completed' => 'Rezervace byla dokončena. Nyní můžete přidat hodnocení.',
            'cancelled' => 'Rezervace byla zrušena.',
        ];

        if (! isset($statusMessages[$booking->status])) {
            return;
        }

        if ($booking->user_id) {
            Notification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking',
                'title' => match ($booking->status) {
                    'confirmed' => 'Rezervace potvrzena',
                    'completed' => 'Rezervace dokončena',
                    'cancelled' => 'Rezervace zrušena',
                },
                'message' => $statusMessages[$booking->status],
                'data' => [
                    'booking_id' => $booking->id,
                    'service_name' => $booking->service->name,
                    'status' => $booking->status,
                ],
                'action_url' => route('bookings.index'),
            ]);
        }

        // If cancelled, also notify provider
        if ($booking->status === 'cancelled') {
            Notification::create([
                'user_id' => $booking->provider_id,
                'type' => 'booking',
                'title' => 'Rezervace zrušena',
                'message' => "Rezervace služby {$booking->service->name} na {$booking->booking_date->format('d. m. Y')} byla zrušena.",
                'data' => [
                    'booking_id' => $booking->id,
                    'service_name' => $booking->service->name,
                ],
                'action_url' => route('vendor.bookings.index'),
            ]);
        }
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
            'title' => 'Nové hodnocení',
            'message' => "{$review->user->name} přidal(a) hodnocení {$review->rating}/5 ke službě {$booking->service->name}.",
            'data' => [
                'review_id' => $review->id,
                'booking_id' => $booking->id,
                'rating' => $review->rating,
                'customer_name' => $review->user->name,
            ],
            'action_url' => route('shops.show', $booking->shop->slug),
        ]);
    }

    /**
     * Create a reminder notification.
     */
    public static function sendReminder(Booking $booking, string $reminderType): void
    {
        $reminders = [
            '24h_customer' => [
                'title' => 'Rezervace je zítra',
                'message' => "Připomínka služby {$booking->service->name} zítra v ".substr($booking->start_time, 0, 5).'.',
            ],
            '24h_provider' => [
                'title' => 'Rezervace je zítra',
                'message' => 'Zítra v '.substr($booking->start_time, 0, 5)." máte rezervaci se zákazníkem {$booking->customer_display_name}.",
            ],
            '1h_customer' => [
                'title' => 'Rezervace začne za hodinu',
                'message' => "Služba {$booking->service->name} začne za jednu hodinu.",
            ],
            '1h_provider' => [
                'title' => 'Rezervace začne za hodinu',
                'message' => "Rezervace se zákazníkem {$booking->customer_display_name} začne za jednu hodinu.",
            ],
        ];

        if (! isset($reminders[$reminderType])) {
            return;
        }

        $userId = str_contains($reminderType, 'customer') ? $booking->user_id : $booking->provider_id;
        if (! $userId) {
            return;
        }

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
