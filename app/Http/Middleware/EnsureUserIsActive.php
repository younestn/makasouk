<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && $user->is_suspended) {
            return response()->json(['message' => __('messages.middleware.account_suspended')], 403);
        }

        return $next($request);
    }
}
