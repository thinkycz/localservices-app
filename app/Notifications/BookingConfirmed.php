<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private Booking $booking
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $service = $this->booking->service;
        $offering = $this->booking->offering;

        return (new MailMessage)
            ->subject('Booking Confirmed - ' . $service->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking has been confirmed.')
            ->line('Service: ' . $service->name)
            ->line('Package: ' . $offering->name)
            ->line('Date: ' . $this->booking->booking_date->format('F j, Y'))
            ->line('Time: ' . $this->booking->start_time)
            ->line('Total: $' . number_format($this->booking->total_price, 2))
            ->action('View Booking', route('bookings.confirmation', $this->booking->id))
            ->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'service_name' => $this->booking->service->name,
            'type' => 'booking_confirmed',
            'message' => 'Your booking for ' . $this->booking->service->name . ' has been confirmed',
        ];
    }
}
