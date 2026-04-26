<?php

namespace App\Filament\Pages;

use App\Mail\TestMailConfigurationMail;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\Mail\MailConfigurationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class MailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.mail-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.mail_settings');
    }

    public function getTitle(): string
    {
        return __('admin.mail.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.mail.subheading');
    }

    public function mount(): void
    {
        $settings = MailSetting::current();

        $this->form->fill([
            'is_enabled' => $settings->is_enabled,
            'driver' => $settings->driver,
            'host' => $settings->host,
            'port' => $settings->port,
            'username' => $settings->username,
            'password' => null,
            'encryption' => $settings->encryption,
            'from_address' => $settings->from_address,
            'from_name' => $settings->from_name,
            'mailer_name' => $settings->mailer_name,
            'timeout_seconds' => $settings->timeout_seconds,
            'queue_mail' => $settings->queue_mail,
            'tailor_email_verification_enabled' => $settings->tailor_email_verification_enabled,
            'tailor_phone_verification_enabled' => $settings->tailor_phone_verification_enabled,
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        $settings = MailSetting::current();

        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make(__('admin.mail.sections.general'))
                    ->description(__('admin.mail.sections.general_description'))
                    ->icon('heroicon-o-power')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('admin.mail.fields.is_enabled'))
                            ->helperText(__('admin.mail.help.is_enabled'))
                            ->inline(false),
                        Forms\Components\Toggle::make('queue_mail')
                            ->label(__('admin.mail.fields.queue_mail'))
                            ->helperText(__('admin.mail.help.queue_mail'))
                            ->inline(false),
                        Forms\Components\Select::make('driver')
                            ->label(__('admin.mail.fields.driver'))
                            ->options(MailSetting::driverOptions())
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('mailer_name')
                            ->label(__('admin.mail.fields.mailer_name'))
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make(__('admin.mail.sections.smtp'))
                    ->description(__('admin.mail.sections.smtp_description'))
                    ->icon('heroicon-o-server')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('host')
                            ->label(__('admin.mail.fields.host'))
                            ->maxLength(255)
                            ->required(fn (Forms\Get $get): bool => $get('driver') === MailSetting::DRIVER_SMTP),
                        Forms\Components\TextInput::make('port')
                            ->label(__('admin.mail.fields.port'))
                            ->integer()
                            ->minValue(1)
                            ->maxValue(65535)
                            ->required(fn (Forms\Get $get): bool => $get('driver') === MailSetting::DRIVER_SMTP),
                        Forms\Components\TextInput::make('username')
                            ->label(__('admin.mail.fields.username'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label(__('admin.mail.fields.password'))
                            ->password()
                            ->revealable()
                            ->placeholder($settings->password ? __('admin.mail.placeholders.secret_saved') : null)
                            ->helperText(__('admin.mail.help.secret_update')),
                        Forms\Components\Select::make('encryption')
                            ->label(__('admin.mail.fields.encryption'))
                            ->options(MailSetting::encryptionOptions())
                            ->placeholder(__('admin.mail.placeholders.no_encryption'))
                            ->native(false),
                        Forms\Components\TextInput::make('timeout_seconds')
                            ->label(__('admin.mail.fields.timeout_seconds'))
                            ->integer()
                            ->minValue(3)
                            ->maxValue(60)
                            ->required(),
                    ]),
                Forms\Components\Section::make(__('admin.mail.sections.sender'))
                    ->description(__('admin.mail.sections.sender_description'))
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('from_address')
                            ->label(__('admin.mail.fields.from_address'))
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('from_name')
                            ->label(__('admin.mail.fields.from_name'))
                            ->required()
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make(__('admin.mail.sections.verification'))
                    ->description(__('admin.mail.sections.verification_description'))
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('tailor_email_verification_enabled')
                            ->label(__('admin.mail.fields.tailor_email_verification_enabled'))
                            ->helperText(__('admin.mail.help.tailor_email_verification_enabled'))
                            ->inline(false),
                        Forms\Components\Toggle::make('tailor_phone_verification_enabled')
                            ->label(__('admin.mail.fields.tailor_phone_verification_enabled'))
                            ->helperText(__('admin.mail.help.tailor_phone_verification_enabled'))
                            ->inline(false),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_test_email')
                ->label(__('admin.mail.actions.send_test'))
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    Forms\Components\TextInput::make('email')
                        ->label(__('admin.mail.fields.test_email'))
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('admin.mail.help.test_email')),
                ])
                ->action(fn (array $data) => $this->sendTestEmail((string) $data['email'])),
        ];
    }

    public function save(): void
    {
        $settings = MailSetting::current();
        $data = $this->form->getState();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $settings->fill($data)->save();
        app(MailConfigurationService::class)->applyRuntimeConfiguration();
        $this->mount();

        Notification::make()
            ->title(__('admin.mail.notifications.saved'))
            ->success()
            ->send();
    }

    private function sendTestEmail(string $email): void
    {
        /** @var User|null $admin */
        $admin = auth()->user();
        $rateLimitKey = 'mail-settings-test:'.($admin?->id ?: request()->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            throw ValidationException::withMessages([
                'email' => [__('admin.mail.validation.test_rate_limited', [
                    'seconds' => RateLimiter::availableIn($rateLimitKey),
                ])],
            ]);
        }

        RateLimiter::hit($rateLimitKey, 300);

        $mailConfig = app(MailConfigurationService::class);
        $mailConfig->applyRuntimeConfiguration();

        if (! $mailConfig->canSend()) {
            Notification::make()
                ->title(__('admin.mail.notifications.disabled_or_incomplete'))
                ->warning()
                ->send();

            return;
        }

        try {
            Mail::to($email)->send(new TestMailConfigurationMail(app()->getLocale()));

            Notification::make()
                ->title(__('admin.mail.notifications.test_sent'))
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('admin.mail.notifications.test_failed'))
                ->body(__('admin.mail.notifications.test_failed_body'))
                ->danger()
                ->send();
        }
    }
}
