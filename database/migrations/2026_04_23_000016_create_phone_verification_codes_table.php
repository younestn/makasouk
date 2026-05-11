<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('phone_verification_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 32);
            $table->string('code_hash', 128);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->dateTime('sent_at');
$table->dateTime('expires_at');
$table->dateTime('verified_at')->nullable();
$table->timestamps();

            $table->index(['user_id', 'expires_at']);
            $table->index(['phone', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_verification_codes');
    }
};

