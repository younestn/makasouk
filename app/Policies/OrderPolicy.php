<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->role === User::ROLE_ADMIN
            || ($user->role === User::ROLE_CUSTOMER && $order->customer_id === $user->id)
            || ($user->role === User::ROLE_TAILOR && $order->tailor_id === $user->id)
            || ($user->role === User::ROLE_TAILOR && $order->tailorOffers()->where('tailor_id', $user->id)->exists());
    }

    public function cancelByCustomer(User $user, Order $order): bool
    {
        return $user->role === User::ROLE_CUSTOMER && $order->customer_id === $user->id;
    }

    public function acceptByTailor(User $user): bool
    {
        return $user->role === User::ROLE_TAILOR;
    }

    public function updateByTailor(User $user, Order $order): bool
    {
        return $user->role === User::ROLE_TAILOR && $order->tailor_id === $user->id;
    }

    public function cancelByTailor(User $user, Order $order): bool
    {
        return $this->updateByTailor($user, $order);
    }

    public function adminAccess(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }
}
