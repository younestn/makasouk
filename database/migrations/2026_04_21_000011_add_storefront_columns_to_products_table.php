<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('short_description')->nullable()->after('slug');
            $table->string('main_image')->nullable()->after('description');
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            $table->unsignedInteger('stock')->default(0)->after('sale_price');
            $table->string('sku')->nullable()->index()->after('stock');
            $table->boolean('is_featured')->default(false)->index()->after('is_active');
            $table->boolean('is_best_seller')->default(false)->index()->after('is_featured');
            $table->timestamp('published_at')->nullable()->index()->after('is_best_seller');
            $table->unsignedInteger('sort_order')->default(0)->index()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'short_description',
                'main_image',
                'sale_price',
                'stock',
                'sku',
                'is_featured',
                'is_best_seller',
                'published_at',
                'sort_order',
            ]);
        });
    }
};

