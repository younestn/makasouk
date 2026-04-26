<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('hero_enabled')->default(true);
            $table->boolean('category_blocks_enabled')->default(true);
            $table->boolean('new_arrivals_enabled')->default(true);
            $table->boolean('best_sellers_enabled')->default(true);
            $table->boolean('category_sections_enabled')->default(true);
            $table->boolean('all_products_enabled')->default(true);
            $table->unsignedSmallInteger('products_per_section')->default(8);
            $table->unsignedSmallInteger('all_products_per_page')->default(12);
            $table->unsignedSmallInteger('featured_categories_limit')->default(4);
            $table->boolean('hero_autoplay')->default(true);
            $table->unsignedSmallInteger('hero_autoplay_delay_ms')->default(6000);
            $table->json('section_order')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};

