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
            return response()->json(['message' => 'Your account is suspended.'], 403);
        }

        return $next($request);
    }
}
