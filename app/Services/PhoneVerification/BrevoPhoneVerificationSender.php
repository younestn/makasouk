<?php

namespace App\Services\PhoneVerification;

use App\Contracts\PhoneVerificationSender;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrevoPhoneVerificationSender implements PhoneVerificationSender
{
    public function __construct(
        private readonly PhoneVerificationMessageBuilder $messageBuilder,
        private readonly array $config,
    ) {
    }

    public function send(User $user, string $phone, string $code, CarbonInterface $expiresAt): void
    {
        $apiKey = (string) Arr::get($this->config, 'api_key');
        $sender = (string) Arr::get($this->config, 'sender', 'MAKASOUK');

        if (blank($apiKey) || blank($sender)) {
            throw new RuntimeException('Brevo SMS configuration is incomplete.');
        }

        $response = Http::timeout((int) Arr::get($this->config, 'timeout', 10))
            ->withHeaders([
                'accept' => 'application/json',
                'api-key' => $apiKey,
            ])
            ->post((string) Arr::get($this->config, 'endpoint', 'https://api.brevo.com/v3/transactionalSMS/sms'), [
                'sender' => $sender,
                'recipient' => $phone,
                'content' => $this->messageBuilder->build($user, $code, $expiresAt),
                'type' => 'transactional',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Brevo SMS request failed with status '.$response->status().'.');
        }
    }
}
