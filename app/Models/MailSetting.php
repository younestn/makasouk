<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class MailSetting extends Model
{
    use HasFactory;

    public const DRIVER_SMTP = 'smtp';
    public const DRIVER_LOG = 'log';
    public const DRIVER_ARRAY = 'array';

    public const ENCRYPTION_TLS = 'tls';
    public const ENCRYPTION_SSL = 'ssl';

    protected $fillable = [
        'is_enabled',
        'driver',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'mailer_name',
        'timeout_seconds',
        'queue_mail',
        'tailor_email_verification_enabled',
        'tailor_phone_verification_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'port' => 'integer',
            'timeout_seconds' => 'integer',
            'queue_mail' => 'boolean',
            'tailor_email_verification_enabled' => 'boolean',
            'tailor_phone_verification_enabled' => 'boolean',
            'password' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => false,
            'driver' => self::DRIVER_LOG,
            'port' => 587,
            'encryption' => self::ENCRYPTION_TLS,
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name', config('app.name', 'MAKASOUK')),
            'mailer_name' => config('app.name', 'MAKASOUK'),
            'timeout_seconds' => 10,
            'queue_mail' => true,
            'tailor_email_verification_enabled' => false,
            'tailor_phone_verification_enabled' => true,
        ]);
    }

    public static function safelyCurrent(): ?self
    {
        try {
            if (! Schema::hasTable('mail_settings')) {
                return null;
            }

            return static::current();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function tailorEmailVerificationEnabled(): bool
    {
        return (bool) (static::safelyCurrent()?->tailor_email_verification_enabled ?? false);
    }

    public static function tailorPhoneVerificationEnabled(): bool
    {
        return (bool) (static::safelyCurrent()?->tailor_phone_verification_enabled ?? true);
    }

    /**
     * @return array<string, string>
     */
    public static function driverOptions(): array
    {
        return [
            self::DRIVER_SMTP => __('admin.mail.drivers.smtp'),
            self::DRIVER_LOG => __('admin.mail.drivers.log'),
            self::DRIVER_ARRAY => __('admin.mail.drivers.array'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function encryptionOptions(): array
    {
        return [
            self::ENCRYPTION_TLS => __('admin.mail.encryptions.tls'),
            self::ENCRYPTION_SSL => __('admin.mail.encryptions.ssl'),
        ];
    }

    public function isConfigured(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        return match ($this->driver) {
            self::DRIVER_SMTP => filled($this->host)
                && filled($this->port)
                && filled($this->from_address),
            self::DRIVER_LOG, self::DRIVER_ARRAY => true,
            default => false,
        };
    }
}
