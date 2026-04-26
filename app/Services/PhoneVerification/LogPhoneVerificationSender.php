<?php

namespace App\Services\PhoneVerification;

use App\Contracts\PhoneVerificationSender;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;

class LogPhoneVerificationSender implements PhoneVerificationSender
{
    public function send(User $user, string $phone, string $code, CarbonInterface $expiresAt): void
    {
        $logPayload = [
            'user_id' => $user->id,
            'phone' => $phone,
            'expires_at' => $expiresAt->toISOString(),
            'driver' => config('services.phone_verification.driver', 'log'),
        ];

        if ((bool) config('services.phone_verification.expose_code_in_logs', false)) {
            $logPayload['otp_code'] = $code;
        }

        Log::channel(config('services.phone_verification.log_channel', config('logging.default')))
            ->info('Phone verification code dispatched', $logPayload);
    }
}
