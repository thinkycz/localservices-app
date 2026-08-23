<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function view(User $user, Review $review): bool
    {
        return $review->is_approved || $review->user_id === $user->id;
    }

    public function manage(User $user, Review $review): bool
    {
        return $review->user_id === $user->id;
    }
}
