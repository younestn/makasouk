<?php

namespace App\Policies;

use App\Models\CustomOrder;
use App\Models\User;

class CustomOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function view(User $user, CustomOrder $customOrder): bool
    {
        return $user->role === User::ROLE_ADMIN
            || ($user->role === User::ROLE_CUSTOMER && (int) $customOrder->customer_id === (int) $user->id)
            || ($user->role === User::ROLE_TAILOR && (int) $customOrder->tailor_id === (int) $user->id);
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_CUSTOMER;
    }

    public function respondToQuote(User $user, CustomOrder $customOrder): bool
    {
        return $user->role === User::ROLE_CUSTOMER
            && (int) $customOrder->customer_id === (int) $user->id;
    }

    public function update(User $user, CustomOrder $customOrder): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function adminAccess(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }
}
