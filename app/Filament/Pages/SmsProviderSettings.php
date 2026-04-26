<?php

namespace App\Filament\Pages;

use App\Models\SmsProviderSetting;
use App\Models\User;
use App\Services\PhoneVerification\PhoneVerificationSenderManager;
use Carbon\CarbonImmutable;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class SmsProviderSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.sms-provider-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.sms_provider_settings');
    }

    public function getTitle(): string
    {
        return __('admin.sms.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.sms.subheading');
    }

    public function mount(): void
    {
        $settings = SmsProviderSetting::current();

        $this->form->fill([
            'is_enabled' => $settings->is_enabled,
            'test_mode' => $settings->test_mode,
            'active_provider' => $settings->active_provider,
            'timeout_seconds' => $settings->timeout_seconds,
            'message_template_en' => $settings->message_template_en,
            'message_template_ar' => $settings->message_template_ar,
            'brevo_api_key' => null,
            'brevo_sender' => $settings->brevo_sender,
            'twilio_account_sid' => $settings->twilio_account_sid,
            'twilio_auth_token' => null,
            'twilio_from_number' => $settings->twilio_from_number,
            'twilio_messaging_service_sid' => $settings->twilio_messaging_service_sid,
            'messagebird_api_key' => null,
            'messagebird_originator' => $settings->messagebird_originator,
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        $settings = SmsProviderSetting::current();

        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make(__('admin.sms.sections.general'))
                    ->description(__('admin.sms.sections.general_description'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('admin.sms.fields.is_enabled'))
                            ->helperText(__('admin.sms.help.is_enabled'))
                            ->default(true)
                            ->inline(false),
                        Forms\Components\Toggle::make('test_mode')
                            ->label(__('admin.sms.fields.test_mode'))
                            ->helperText(__('admin.sms.help.test_mode'))
                            ->default(false)
                            ->inline(false),
                        Forms\Components\Select::make('active_provider')
                            ->label(__('admin.sms.fields.active_provider'))
                            ->options(SmsProviderSetting::providerOptions())
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('timeout_seconds')
                            ->label(__('admin.sms.fields.timeout_seconds'))
                            ->integer()
                            ->minValue(3)
                            ->maxValue(30)
                            ->required(),
                    ]),
                Forms\Components\Section::make(__('admin.sms.sections.message_template'))
                    ->description(__('admin.sms.sections.message_template_description'))
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('message_template_en')
                            ->label(__('admin.sms.fields.message_template_en'))
                            ->helperText(__('admin.sms.help.message_template'))
                            ->rows(3),
                        Forms\Components\Textarea::make('message_template_ar')
                            ->label(__('admin.sms.fields.message_template_ar'))
                            ->helperText(__('admin.sms.help.message_template'))
                            ->rows(3),
                    ]),
                Forms\Components\Section::make(__('admin.sms.sections.brevo'))
                    ->description(__('admin.sms.sections.brevo_description'))
                    ->icon('heroicon-o-paper-airplane')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('brevo_api_key')
                            ->label(__('admin.sms.fields.brevo_api_key'))
                            ->password()
                            ->revealable()
                            ->placeholder($settings->brevo_api_key ? __('admin.sms.placeholders.secret_saved') : null)
                            ->helperText(__('admin.sms.help.secret_update')),
                        Forms\Components\TextInput::make('brevo_sender')
                            ->label(__('admin.sms.fields.brevo_sender'))
                            ->maxLength(64)
                            ->placeholder('MAKASOUK'),
                    ]),
                Forms\Components\Section::make(__('admin.sms.sections.twilio'))
                    ->description(__('admin.sms.sections.twilio_description'))
                    ->icon('heroicon-o-signal')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('twilio_account_sid')
                            ->label(__('admin.sms.fields.twilio_account_sid'))
                            ->maxLength(128),
                        Forms\Components\TextInput::make('twilio_auth_token')
                            ->label(__('admin.sms.fields.twilio_auth_token'))
                            ->password()
                            ->revealable()
                            ->placeholder($settings->twilio_auth_token ? __('admin.sms.placeholders.secret_saved') : null)
                            ->helperText(__('admin.sms.help.secret_update')),
                        Forms\Components\TextInput::make('twilio_from_number')
                            ->label(__('admin.sms.fields.twilio_from_number'))
                            ->maxLength(32)
                            ->helperText(__('admin.sms.help.twilio_sender')),
                        Forms\Components\TextInput::make('twilio_messaging_service_sid')
                            ->label(__('admin.sms.fields.twilio_messaging_service_sid'))
                            ->maxLength(128)
                            ->helperText(__('admin.sms.help.twilio_sender')),
                    ]),
                Forms\Components\Section::make(__('admin.sms.sections.messagebird'))
                    ->description(__('admin.sms.sections.messagebird_description'))
                    ->icon('heroicon-o-envelope')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('messagebird_api_key')
                            ->label(__('admin.sms.fields.messagebird_api_key'))
                            ->password()
                            ->revealable()
                            ->placeholder($settings->messagebird_api_key ? __('admin.sms.placeholders.secret_saved') : null)
                            ->helperText(__('admin.sms.help.secret_update')),
                        Forms\Components\TextInput::make('messagebird_originator')
                            ->label(__('admin.sms.fields.messagebird_originator'))
                            ->maxLength(64)
                            ->placeholder('MAKASOUK'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_test_sms')
                ->label(__('admin.sms.actions.send_test'))
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    Forms\Components\TextInput::make('phone')
                        ->label(__('admin.sms.fields.test_phone'))
                        ->tel()
                        ->required()
                        ->maxLength(32)
                        ->helperText(__('admin.sms.help.test_phone')),
                ])
                ->action(fn (array $data) => $this->sendTestSms((string) $data['phone'])),
        ];
    }

    public function save(): void
    {
        $settings = SmsProviderSetting::current();
        $data = $this->form->getState();

        foreach (['brevo_api_key', 'twilio_auth_token', 'messagebird_api_key'] as $secretField) {
            if (blank($data[$secretField] ?? null)) {
                unset($data[$secretField]);
            }
        }

        $settings->fill($data)->save();
        $this->mount();

        Notification::make()
            ->title(__('admin.sms.notifications.saved'))
            ->success()
            ->send();
    }

    private function sendTestSms(string $phone): void
    {
        /** @var User|null $admin */
        $admin = auth()->user();
        $rateLimitKey = 'sms-provider-test:'.($admin?->id ?: request()->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            throw ValidationException::withMessages([
                'phone' => [__('admin.sms.validation.test_rate_limited', [
                    'seconds' => RateLimiter::availableIn($rateLimitKey),
                ])],
            ]);
        }

        RateLimiter::hit($rateLimitKey, 300);

        try {
            app(PhoneVerificationSenderManager::class)->send(
                $admin ?? new User(['name' => 'Admin']),
                $phone,
                '123456',
                CarbonImmutable::now()->addMinutes(10),
            );

            Notification::make()
                ->title(__('admin.sms.notifications.test_sent'))
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('admin.sms.notifications.test_failed'))
                ->body(__('admin.sms.notifications.test_failed_body'))
                ->danger()
                ->send();
        }
    }
}
