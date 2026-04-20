<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureTailorIsApproved
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && $user->role === 'tailor' && $user->approved_at === null) {
            return response()->json(['message' => 'Tailor account is pending approval.'], 403);
        }

        return $next($request);
    }
}
