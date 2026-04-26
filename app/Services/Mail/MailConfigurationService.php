<?php

namespace App\Services\Mail;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class MailConfigurationService
{
    public function applyRuntimeConfiguration(): void
    {
        $settings = MailSetting::safelyCurrent();

        if ($settings === null || ! $settings->isConfigured()) {
            return;
        }

        $driver = $settings->driver ?: MailSetting::DRIVER_LOG;

        Config::set('mail.default', $driver);
        Config::set('mail.from.address', $settings->from_address ?: config('mail.from.address'));
        Config::set('mail.from.name', $settings->from_name ?: config('mail.from.name'));

        if ($driver === MailSetting::DRIVER_SMTP) {
            Config::set('mail.mailers.smtp.host', $settings->host);
            Config::set('mail.mailers.smtp.port', $settings->port);
            Config::set('mail.mailers.smtp.username', $settings->username);
            Config::set('mail.mailers.smtp.password', $settings->password);
            Config::set('mail.mailers.smtp.encryption', $settings->encryption);
            Config::set('mail.mailers.smtp.timeout', max(3, (int) $settings->timeout_seconds));
        }
    }

    public function canSend(): bool
    {
        $settings = MailSetting::safelyCurrent();

        return $settings?->isConfigured() ?? false;
    }

    public function shouldQueue(): bool
    {
        return (bool) (MailSetting::safelyCurrent()?->queue_mail ?? true);
    }

    public function logSkipped(string $context, array $extra = []): void
    {
        Log::notice('Mail delivery skipped because admin mail settings are disabled or incomplete.', array_merge([
            'context' => $context,
        ], $extra));
    }
}
