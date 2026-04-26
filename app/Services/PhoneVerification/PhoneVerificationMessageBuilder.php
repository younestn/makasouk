<?php

namespace App\Services\PhoneVerification;

use App\Models\SmsProviderSetting;
use App\Models\User;
use Carbon\CarbonInterface;

class PhoneVerificationMessageBuilder
{
    public function build(User $user, string $code, CarbonInterface $expiresAt): string
    {
        $minutes = max(1, (int) now()->diffInMinutes($expiresAt, false));
        $settings = SmsProviderSetting::query()->first();
        $template = $settings?->localizedMessageTemplate();

        if (filled($template)) {
            return strtr((string) $template, [
                '{app}' => config('app.name', 'MAKASOUK'),
                '{code}' => $code,
                '{minutes}' => (string) $minutes,
                '{phone}' => (string) $user->phone,
            ]);
        }

        return trans('messages.auth.phone_verification_sms', [
            'app' => config('app.name', 'MAKASOUK'),
            'code' => $code,
            'minutes' => $minutes,
        ]);
    }
}
