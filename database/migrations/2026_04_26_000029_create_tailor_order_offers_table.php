<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tailor_order_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tailor_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('unread')->index();
            $table->decimal('distance_km', 10, 3)->nullable();
            $table->string('reason', 64)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'tailor_id']);
            $table->index(['tailor_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tailor_order_offers');
    }
};
