<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class AdminLogin extends Login
{
    protected static string $view = 'filament.pages.auth.admin-login';

    public function getTitle(): string | Htmlable
    {
        return __('admin.auth.title');
    }

    public function getHeading(): string | Htmlable
    {
        return __('admin.auth.heading');
    }

    public function getSubHeading(): string | Htmlable | null
    {
        return __('admin.auth.subheading');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('admin.auth.email'))
            ->placeholder(__('admin.auth.email_placeholder'))
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->prefixIcon('heroicon-o-envelope')
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('admin.auth.password'))
            ->placeholder(__('admin.auth.password_placeholder'))
            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="3"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
            ->password()
            ->revealable(true)
            ->prefixIcon('heroicon-o-lock-closed')
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }
}
