<?php

namespace App\Providers;

use App\Contracts\PhoneVerificationSender;
use App\Services\Mail\MailConfigurationService;
use App\Services\PhoneVerification\PhoneVerificationSenderManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PhoneVerificationSender::class, PhoneVerificationSenderManager::class);
    }

    public function boot(): void
    {
        app(MailConfigurationService::class)->applyRuntimeConfiguration();
    }
}
