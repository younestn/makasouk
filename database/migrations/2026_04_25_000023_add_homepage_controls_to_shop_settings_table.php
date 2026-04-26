<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shop_settings', function (Blueprint $table): void {
            $table->boolean('homepage_hero_enabled')->default(true)->after('all_products_enabled');
            $table->boolean('homepage_stats_enabled')->default(true)->after('homepage_hero_enabled');
            $table->boolean('homepage_best_sellers_enabled')->default(true)->after('homepage_stats_enabled');
            $table->boolean('homepage_testimonials_enabled')->default(true)->after('homepage_best_sellers_enabled');
            $table->string('homepage_badge_en')->nullable()->after('hero_autoplay_delay_ms');
            $table->string('homepage_badge_ar')->nullable()->after('homepage_badge_en');
            $table->string('homepage_title_en')->nullable()->after('homepage_badge_ar');
            $table->string('homepage_title_ar')->nullable()->after('homepage_title_en');
            $table->text('homepage_subtitle_en')->nullable()->after('homepage_title_ar');
            $table->text('homepage_subtitle_ar')->nullable()->after('homepage_subtitle_en');
            $table->string('homepage_primary_cta_label_en')->nullable()->after('homepage_subtitle_ar');
            $table->string('homepage_primary_cta_label_ar')->nullable()->after('homepage_primary_cta_label_en');
            $table->string('homepage_primary_cta_url')->default('/shop')->after('homepage_primary_cta_label_ar');
            $table->string('homepage_secondary_cta_label_en')->nullable()->after('homepage_primary_cta_url');
            $table->string('homepage_secondary_cta_label_ar')->nullable()->after('homepage_secondary_cta_label_en');
            $table->string('homepage_secondary_cta_url')->default('/how-it-works')->after('homepage_secondary_cta_label_ar');
            $table->json('homepage_section_order')->nullable()->after('section_order');
        });
    }

    public function down(): void
    {
        Schema::table('shop_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'homepage_hero_enabled',
                'homepage_stats_enabled',
                'homepage_best_sellers_enabled',
                'homepage_testimonials_enabled',
                'homepage_badge_en',
                'homepage_badge_ar',
                'homepage_title_en',
                'homepage_title_ar',
                'homepage_subtitle_en',
                'homepage_subtitle_ar',
                'homepage_primary_cta_label_en',
                'homepage_primary_cta_label_ar',
                'homepage_primary_cta_url',
                'homepage_secondary_cta_label_en',
                'homepage_secondary_cta_label_ar',
                'homepage_secondary_cta_url',
                'homepage_section_order',
            ]);
        });
    }
};
