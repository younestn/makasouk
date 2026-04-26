<?php

namespace App\Services\PhoneVerification;

use App\Contracts\PhoneVerificationSender;
use App\Models\SmsProviderSetting;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class PhoneVerificationSenderManager implements PhoneVerificationSender
{
    public function __construct(
        private readonly PhoneVerificationMessageBuilder $messageBuilder,
        private readonly LogPhoneVerificationSender $logSender,
    ) {
    }

    public function send(User $user, string $phone, string $code, CarbonInterface $expiresAt): void
    {
        $provider = $this->activeProvider();
        $sender = $this->senderFor($provider);

        try {
            $sender->send($user, $phone, $code, $expiresAt);
        } catch (Throwable $exception) {
            Log::warning('Phone verification SMS provider failed', [
                'provider' => $provider,
                'user_id' => $user->id,
                'phone' => $phone,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Phone verification SMS delivery failed.', previous: $exception);
        }
    }

    public function activeProvider(): string
    {
        $settings = SmsProviderSetting::query()->first();
        $provider = $settings?->active_provider ?: (string) config('services.phone_verification.driver', SmsProviderSetting::PROVIDER_LOG);

        return in_array($provider, SmsProviderSetting::PROVIDERS, true)
            ? $provider
            : SmsProviderSetting::PROVIDER_LOG;
    }

    public function senderFor(?string $provider = null): PhoneVerificationSender
    {
        $settings = SmsProviderSetting::query()->first();
        $provider ??= $this->activeProvider();

        if (($settings !== null && ! $settings->is_enabled) || ! $this->isConfigured($provider, $settings)) {
            if ($provider !== SmsProviderSetting::PROVIDER_LOG) {
                Log::notice('Phone verification provider is not configured; using log fallback.', [
                    'provider' => $provider,
                ]);
            }

            return $this->logSender;
        }

        return match ($provider) {
            SmsProviderSetting::PROVIDER_BREVO => new BrevoPhoneVerificationSender($this->messageBuilder, $this->brevoConfig($settings)),
            SmsProviderSetting::PROVIDER_TWILIO => new TwilioPhoneVerificationSender($this->messageBuilder, $this->twilioConfig($settings)),
            SmsProviderSetting::PROVIDER_MESSAGEBIRD => new MessageBirdPhoneVerificationSender($this->messageBuilder, $this->messageBirdConfig($settings)),
            default => $this->logSender,
        };
    }

    private function isConfigured(string $provider, ?SmsProviderSetting $settings): bool
    {
        if ($settings?->isProviderConfigured($provider)) {
            return true;
        }

        return match ($provider) {
            SmsProviderSetting::PROVIDER_LOG => true,
            SmsProviderSetting::PROVIDER_BREVO => filled(config('services.phone_verification.brevo.api_key'))
                && filled(config('services.phone_verification.brevo.sender')),
            SmsProviderSetting::PROVIDER_TWILIO => filled(config('services.phone_verification.twilio.account_sid'))
                && filled(config('services.phone_verification.twilio.auth_token'))
                && (filled(config('services.phone_verification.twilio.from_number'))
                    || filled(config('services.phone_verification.twilio.messaging_service_sid'))),
            SmsProviderSetting::PROVIDER_MESSAGEBIRD => filled(config('services.phone_verification.messagebird.api_key'))
                && filled(config('services.phone_verification.messagebird.originator')),
            default => false,
        };
    }

    private function timeout(?SmsProviderSetting $settings): int
    {
        return max(3, (int) ($settings?->timeout_seconds ?: config('services.phone_verification.timeout_seconds', 10)));
    }

    /**
     * @return array<string, mixed>
     */
    private function brevoConfig(?SmsProviderSetting $settings): array
    {
        return [
            'api_key' => $settings?->brevo_api_key ?: config('services.phone_verification.brevo.api_key'),
            'sender' => $settings?->brevo_sender ?: config('services.phone_verification.brevo.sender', 'MAKASOUK'),
            'endpoint' => config('services.phone_verification.brevo.endpoint', 'https://api.brevo.com/v3/transactionalSMS/sms'),
            'timeout' => $this->timeout($settings),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function twilioConfig(?SmsProviderSetting $settings): array
    {
        return [
            'account_sid' => $settings?->twilio_account_sid ?: config('services.phone_verification.twilio.account_sid'),
            'auth_token' => $settings?->twilio_auth_token ?: config('services.phone_verification.twilio.auth_token'),
            'from_number' => $settings?->twilio_from_number ?: config('services.phone_verification.twilio.from_number'),
            'messaging_service_sid' => $settings?->twilio_messaging_service_sid ?: config('services.phone_verification.twilio.messaging_service_sid'),
            'timeout' => $this->timeout($settings),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function messageBirdConfig(?SmsProviderSetting $settings): array
    {
        return [
            'api_key' => $settings?->messagebird_api_key ?: config('services.phone_verification.messagebird.api_key'),
            'originator' => $settings?->messagebird_originator ?: config('services.phone_verification.messagebird.originator', 'MAKASOUK'),
            'endpoint' => config('services.phone_verification.messagebird.endpoint', 'https://rest.messagebird.com/messages'),
            'timeout' => $this->timeout($settings),
        ];
    }
}
