<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsProviderSetting extends Model
{
    use HasFactory;

    public const PROVIDER_LOG = 'log';
    public const PROVIDER_BREVO = 'brevo';
    public const PROVIDER_TWILIO = 'twilio';
    public const PROVIDER_MESSAGEBIRD = 'messagebird';

    /**
     * @var array<int, string>
     */
    public const PROVIDERS = [
        self::PROVIDER_LOG,
        self::PROVIDER_BREVO,
        self::PROVIDER_TWILIO,
        self::PROVIDER_MESSAGEBIRD,
    ];

    protected $fillable = [
        'is_enabled',
        'test_mode',
        'active_provider',
        'timeout_seconds',
        'message_template_en',
        'message_template_ar',
        'brevo_api_key',
        'brevo_sender',
        'twilio_account_sid',
        'twilio_auth_token',
        'twilio_from_number',
        'twilio_messaging_service_sid',
        'messagebird_api_key',
        'messagebird_originator',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'test_mode' => 'boolean',
            'timeout_seconds' => 'integer',
            'brevo_api_key' => 'encrypted',
            'twilio_auth_token' => 'encrypted',
            'messagebird_api_key' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => true,
            'test_mode' => false,
            'active_provider' => self::PROVIDER_LOG,
            'timeout_seconds' => 10,
            'brevo_sender' => 'MAKASOUK',
            'messagebird_originator' => 'MAKASOUK',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function providerOptions(): array
    {
        return [
            self::PROVIDER_LOG => __('admin.sms.providers.log'),
            self::PROVIDER_BREVO => __('admin.sms.providers.brevo'),
            self::PROVIDER_TWILIO => __('admin.sms.providers.twilio'),
            self::PROVIDER_MESSAGEBIRD => __('admin.sms.providers.messagebird'),
        ];
    }

    public function isProviderConfigured(?string $provider = null): bool
    {
        return match ($provider ?? $this->active_provider) {
            self::PROVIDER_LOG => true,
            self::PROVIDER_BREVO => filled($this->brevo_api_key) && filled($this->brevo_sender),
            self::PROVIDER_TWILIO => filled($this->twilio_account_sid)
                && filled($this->twilio_auth_token)
                && (filled($this->twilio_from_number) || filled($this->twilio_messaging_service_sid)),
            self::PROVIDER_MESSAGEBIRD => filled($this->messagebird_api_key) && filled($this->messagebird_originator),
            default => false,
        };
    }

    public function localizedMessageTemplate(): ?string
    {
        $locale = app()->getLocale();

        return filled($this->{"message_template_{$locale}"})
            ? $this->{"message_template_{$locale}"}
            : $this->message_template_en;
    }
}
