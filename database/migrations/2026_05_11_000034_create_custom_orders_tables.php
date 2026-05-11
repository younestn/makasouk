<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tailor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 160);
            $table->string('tailor_specialty', 120)->nullable()->index();
            $table->string('fabric_type', 120)->nullable();
            $table->json('measurements')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            $table->string('delivery_work_wilaya', 120)->nullable();
            $table->string('status', 32)->default('placed')->index();
            $table->decimal('quote_amount', 12, 2)->nullable();
            $table->text('quote_note')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->text('quote_rejection_note')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->json('assignment_meta')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['tailor_id', 'status']);
        });

        Schema::create('custom_order_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('custom_order_id')->constrained('custom_orders')->cascadeOnDelete();
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_order_images');
        Schema::dropIfExists('custom_orders');
    }
};
