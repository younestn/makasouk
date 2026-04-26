<?php

namespace App\Http\Middleware;

use App\Models\MailSetting;
use Closure;
use Illuminate\Http\Request;

class EnsureTailorIsApproved
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if ($user && $user->role === 'tailor' && $user->approved_at === null) {
            return response()->json(['message' => __('messages.middleware.tailor_pending_approval')], 403);
        }

        if (
            $user
            && $user->role === 'tailor'
            && MailSetting::tailorPhoneVerificationEnabled()
            && filled($user->phone)
            && $user->phone_verified_at === null
        ) {
            return response()->json(['message' => __('messages.middleware.tailor_phone_verification_required')], 403);
        }

        if (
            $user
            && $user->role === 'tailor'
            && MailSetting::tailorEmailVerificationEnabled()
            && ! $user->hasVerifiedEmail()
        ) {
            return response()->json(['message' => __('messages.middleware.tailor_email_verification_required')], 403);
        }

        return $next($request);
    }
}
