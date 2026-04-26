<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->string('driver', 32)->default('log');
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('encryption', 16)->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->string('mailer_name')->nullable();
            $table->unsignedTinyInteger('timeout_seconds')->default(10);
            $table->boolean('queue_mail')->default(true);
            $table->boolean('tailor_email_verification_enabled')->default(false);
            $table->boolean('tailor_phone_verification_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};
