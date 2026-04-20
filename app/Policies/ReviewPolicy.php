<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Order $order): bool
    {
        return $user->role === User::ROLE_CUSTOMER && $order->customer_id === $user->id;
    }

    public function view(User $user, Review $review): bool
    {
        return $user->role === User::ROLE_ADMIN
            || $review->customer_id === $user->id
            || $review->tailor_id === $user->id;
    }
}
