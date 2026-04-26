<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sms_provider_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('test_mode')->default(false);
            $table->string('active_provider', 32)->default('log');
            $table->unsignedTinyInteger('timeout_seconds')->default(10);
            $table->text('message_template_en')->nullable();
            $table->text('message_template_ar')->nullable();
            $table->text('brevo_api_key')->nullable();
            $table->string('brevo_sender', 64)->nullable();
            $table->string('twilio_account_sid', 128)->nullable();
            $table->text('twilio_auth_token')->nullable();
            $table->string('twilio_from_number', 32)->nullable();
            $table->string('twilio_messaging_service_sid', 128)->nullable();
            $table->text('messagebird_api_key')->nullable();
            $table->string('messagebird_originator', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_provider_settings');
    }
};
