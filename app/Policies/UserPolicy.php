<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manage(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function suspend(User $user, User $target): bool
    {
        return $user->role === User::ROLE_ADMIN && $target->role !== User::ROLE_ADMIN;
    }
}
