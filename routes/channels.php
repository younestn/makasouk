<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes([
    'middleware' => ['auth:sanctum', 'active'],
]);

Broadcast::channel('tailor.{id}', function ($user, int $id): bool {
    return $user->role === User::ROLE_TAILOR
        && $user->id === $id
        && ! $user->is_suspended
        && $user->approved_at !== null;
});

Broadcast::channel('customer.{id}', function ($user, int $id): bool {
    return $user->role === User::ROLE_CUSTOMER
        && $user->id === $id
        && ! $user->is_suspended;
});

Broadcast::channel('admin.{id}', function ($user, int $id): bool {
    return $user->role === User::ROLE_ADMIN
        && $user->id === $id
        && ! $user->is_suspended;
});
