<?php

namespace App\Services\PhoneVerification;

use App\Contracts\PhoneVerificationSender;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MessageBirdPhoneVerificationSender implements PhoneVerificationSender
{
    public function __construct(
        private readonly PhoneVerificationMessageBuilder $messageBuilder,
        private readonly array $config,
    ) {
    }

    public function send(User $user, string $phone, string $code, CarbonInterface $expiresAt): void
    {
        $apiKey = (string) Arr::get($this->config, 'api_key');
        $originator = (string) Arr::get($this->config, 'originator', 'MAKASOUK');

        if (blank($apiKey) || blank($originator)) {
            throw new RuntimeException('MessageBird SMS configuration is incomplete.');
        }

        $response = Http::timeout((int) Arr::get($this->config, 'timeout', 10))
            ->withToken($apiKey, 'AccessKey')
            ->acceptJson()
            ->post((string) Arr::get($this->config, 'endpoint', 'https://rest.messagebird.com/messages'), [
                'originator' => $originator,
                'recipients' => [$phone],
                'body' => $this->messageBuilder->build($user, $code, $expiresAt),
            ]);

        if ($response->failed()) {
            throw new RuntimeException('MessageBird SMS request failed with status '.$response->status().'.');
        }
    }
}
