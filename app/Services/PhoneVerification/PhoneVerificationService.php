<?php

namespace App\Services\PhoneVerification;

use App\Contracts\PhoneVerificationSender;
use App\Models\MailSetting;
use App\Models\PhoneVerificationCode;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PhoneVerificationService
{
    private const SEND_LIMIT_PER_HOUR = 5;
    private const VERIFY_LIMIT_PER_15_MINUTES = 10;
    private const CODE_MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly PhoneVerificationSender $sender,
    ) {
    }

    /**
     * @return array{expires_at: CarbonImmutable, retry_after_seconds: int}
     */
    public function send(User $user): array
    {
        if (! MailSetting::tailorPhoneVerificationEnabled()) {
            throw ValidationException::withMessages([
                'phone' => [__('messages.auth.phone_verification_disabled')],
            ]);
        }

        if (blank($user->phone)) {
            throw ValidationException::withMessages([
                'phone' => ['A phone number is required before verification can be sent.'],
            ]);
        }

        if ($user->phone_verified_at !== null) {
            throw ValidationException::withMessages([
                'code' => ['Phone number is already verified.'],
            ]);
        }

        $sendKey = $this->sendRateLimitKey($user->id);
        if (RateLimiter::tooManyAttempts($sendKey, self::SEND_LIMIT_PER_HOUR)) {
            $retryAfterSeconds = RateLimiter::availableIn($sendKey);

            throw ValidationException::withMessages([
                'code' => ["Too many verification requests. Try again in {$retryAfterSeconds} seconds."],
            ]);
        }

        /** @var PhoneVerificationCode|null $latestPendingCode */
        $latestPendingCode = PhoneVerificationCode::query()
            ->where('user_id', $user->id)
            ->where('phone', (string) $user->phone)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (
            $latestPendingCode !== null
            && $latestPendingCode->sent_at !== null
            && $latestPendingCode->sent_at->addSeconds($this->resendCooldownSeconds())->isFuture()
        ) {
            $retryAfterSeconds = now()->diffInSeconds(
                $latestPendingCode->sent_at->copy()->addSeconds($this->resendCooldownSeconds()),
                false,
            );

            throw ValidationException::withMessages([
                'code' => ['Please wait before requesting another code.'],
                'retry_after_seconds' => [max(1, (int) $retryAfterSeconds)],
            ]);
        }

        PhoneVerificationCode::query()
            ->where('user_id', $user->id)
            ->whereNull('verified_at')
            ->delete();

        $code = (string) random_int(100000, 999999);
        $expiresAt = CarbonImmutable::now()->addMinutes($this->codeExpiresInMinutes());

        $verification = PhoneVerificationCode::query()->create([
            'user_id' => $user->id,
            'phone' => (string) $user->phone,
            'code_hash' => $this->hashCode((string) $user->phone, $code),
            'attempts' => 0,
            'sent_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        try {
            $this->sender->send($user, (string) $user->phone, $code, $expiresAt);
        } catch (\Throwable $exception) {
            $verification->delete();

            throw ValidationException::withMessages([
                'phone' => [__('messages.auth.phone_verification_send_failed')],
            ]);
        }

        RateLimiter::hit($sendKey, 3600);

        return [
            'expires_at' => $expiresAt,
            'retry_after_seconds' => $this->resendCooldownSeconds(),
        ];
    }

    public function verify(User $user, string $code): void
    {
        if (blank($user->phone)) {
            throw ValidationException::withMessages([
                'phone' => ['No phone number exists for this account.'],
            ]);
        }

        if ($user->phone_verified_at !== null) {
            return;
        }

        $verifyKey = $this->verifyRateLimitKey($user->id);
        if (RateLimiter::tooManyAttempts($verifyKey, self::VERIFY_LIMIT_PER_15_MINUTES)) {
            $retryAfterSeconds = RateLimiter::availableIn($verifyKey);

            throw ValidationException::withMessages([
                'code' => ["Too many verification attempts. Try again in {$retryAfterSeconds} seconds."],
            ]);
        }

        /** @var PhoneVerificationCode|null $verification */
        $verification = PhoneVerificationCode::query()
            ->where('user_id', $user->id)
            ->where('phone', (string) $user->phone)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if ($verification === null || $verification->expires_at->isPast()) {
            RateLimiter::hit($verifyKey, 900);

            throw ValidationException::withMessages([
                'code' => ['Verification code is invalid or expired. Request a new code.'],
            ]);
        }

        if ($verification->attempts >= self::CODE_MAX_ATTEMPTS) {
            RateLimiter::hit($verifyKey, 900);

            throw ValidationException::withMessages([
                'code' => ['Verification attempts exceeded for this code. Request a new code.'],
            ]);
        }

        $expectedHash = $this->hashCode((string) $user->phone, $code);
        if (! hash_equals($verification->code_hash, $expectedHash)) {
            $verification->increment('attempts');
            RateLimiter::hit($verifyKey, 900);

            throw ValidationException::withMessages([
                'code' => ['The verification code is incorrect.'],
            ]);
        }

        $verification->forceFill([
            'verified_at' => now(),
        ])->save();

        $user->forceFill([
            'phone_verified_at' => now(),
        ])->save();

        RateLimiter::clear($verifyKey);
    }

    private function hashCode(string $phone, string $code): string
    {
        return hash('sha256', "{$phone}|{$code}|".config('app.key'));
    }

    private function sendRateLimitKey(int $userId): string
    {
        return "phone-verification:send:{$userId}";
    }

    private function verifyRateLimitKey(int $userId): string
    {
        return "phone-verification:verify:{$userId}";
    }

    private function codeExpiresInMinutes(): int
    {
        return max(1, (int) config('services.phone_verification.code_expires_in_minutes', 10));
    }

    private function resendCooldownSeconds(): int
    {
        return max(10, (int) config('services.phone_verification.resend_cooldown_seconds', 60));
    }
}
