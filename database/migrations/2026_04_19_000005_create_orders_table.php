<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tailor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->json('measurements');
            $table->decimal('delivery_latitude', 10, 7);
            $table->decimal('delivery_longitude', 10, 7);
            $table->point('delivery_location')->nullable();
            $table->enum('status', ['pending', 'broadcasting', 'accepted', 'in_progress', 'completed', 'cancelled'])
                ->default('pending')
                ->index();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['tailor_id', 'status']);
            $table->spatialIndex('delivery_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
