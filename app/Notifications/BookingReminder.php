<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminder extends Notification implements ShouldQueue
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
        $hoursUntil = now()->diffInHours($this->booking->booking_date);

        return (new MailMessage)
            ->subject('Reminder: Upcoming Booking - ' . $service->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder about your upcoming booking.')
            ->line('Service: ' . $service->name)
            ->line('Date: ' . $this->booking->booking_date->format('F j, Y'))
            ->line('Time: ' . $this->booking->start_time)
            ->line('Location: ' . $service->address)
            ->line('You have approximately ' . $hoursUntil . ' hours until your appointment.')
            ->action('View Booking Details', route('bookings.confirmation', $this->booking->id))
            ->line('We look forward to serving you!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'service_name' => $this->booking->service->name,
            'type' => 'booking_reminder',
            'message' => 'Reminder: Your booking for ' . $this->booking->service->name . ' is coming up',
        ];
    }
}
