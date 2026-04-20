<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes([
    'middleware' => ['auth:sanctum'],
]);

Broadcast::channel('tailor.{id}', function ($user, int $id): bool {
    return $user->role === 'tailor' && $user->id === $id && ! $user->is_suspended;
});

Broadcast::channel('customer.{id}', function ($user, int $id): bool {
    return $user->role === 'customer' && $user->id === $id && ! $user->is_suspended;
});

Broadcast::channel('admin.{id}', function ($user, int $id): bool {
    return $user->role === 'admin' && $user->id === $id && ! $user->is_suspended;
});
