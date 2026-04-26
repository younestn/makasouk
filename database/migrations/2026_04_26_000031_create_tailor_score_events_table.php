<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tailor_score_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tailor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 64);
            $table->smallInteger('delta');
            $table->unsignedTinyInteger('score_after');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['tailor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tailor_score_events');
    }
};
