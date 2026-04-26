<?php

namespace App\Services\PhoneVerification;

use App\Contracts\PhoneVerificationSender;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TwilioPhoneVerificationSender implements PhoneVerificationSender
{
    public function __construct(
        private readonly PhoneVerificationMessageBuilder $messageBuilder,
        private readonly array $config,
    ) {
    }

    public function send(User $user, string $phone, string $code, CarbonInterface $expiresAt): void
    {
        $accountSid = (string) Arr::get($this->config, 'account_sid');
        $authToken = (string) Arr::get($this->config, 'auth_token');
        $fromNumber = (string) Arr::get($this->config, 'from_number');
        $messagingServiceSid = (string) Arr::get($this->config, 'messaging_service_sid');

        if (blank($accountSid) || blank($authToken) || (blank($fromNumber) && blank($messagingServiceSid))) {
            throw new RuntimeException('Twilio SMS configuration is incomplete.');
        }

        $payload = [
            'To' => $phone,
            'Body' => $this->messageBuilder->build($user, $code, $expiresAt),
        ];

        if (filled($messagingServiceSid)) {
            $payload['MessagingServiceSid'] = $messagingServiceSid;
        } else {
            $payload['From'] = $fromNumber;
        }

        $response = Http::timeout((int) Arr::get($this->config, 'timeout', 10))
            ->asForm()
            ->withBasicAuth($accountSid, $authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", $payload);

        if ($response->failed()) {
            throw new RuntimeException('Twilio SMS request failed with status '.$response->status().'.');
        }
    }
}
