<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id || $this->manage($user, $booking);
    }

    public function manage(User $user, Booking $booking): bool
    {
        return $booking->provider_id === $user->id
            && $booking->shop()->where('user_id', $user->id)->exists();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }

    public function review(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id && $booking->status === 'completed';
    }
}
