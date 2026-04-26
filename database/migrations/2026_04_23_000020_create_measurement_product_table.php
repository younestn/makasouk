<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('measurement_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('measurement_id')->constrained('measurements')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['measurement_id', 'product_id']);
            $table->index(['product_id', 'measurement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_product');
    }
};
