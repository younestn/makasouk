<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hero_enabled',
        'category_blocks_enabled',
        'new_arrivals_enabled',
        'best_sellers_enabled',
        'category_sections_enabled',
        'all_products_enabled',
        'homepage_hero_enabled',
        'homepage_stats_enabled',
        'homepage_best_sellers_enabled',
        'homepage_testimonials_enabled',
        'products_per_section',
        'all_products_per_page',
        'featured_categories_limit',
        'hero_autoplay',
        'hero_autoplay_delay_ms',
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
        'section_order',
        'homepage_section_order',
    ];

    protected function casts(): array
    {
        return [
            'hero_enabled' => 'boolean',
            'category_blocks_enabled' => 'boolean',
            'new_arrivals_enabled' => 'boolean',
            'best_sellers_enabled' => 'boolean',
            'category_sections_enabled' => 'boolean',
            'all_products_enabled' => 'boolean',
            'homepage_hero_enabled' => 'boolean',
            'homepage_stats_enabled' => 'boolean',
            'homepage_best_sellers_enabled' => 'boolean',
            'homepage_testimonials_enabled' => 'boolean',
            'hero_autoplay' => 'boolean',
            'hero_autoplay_delay_ms' => 'integer',
            'products_per_section' => 'integer',
            'all_products_per_page' => 'integer',
            'featured_categories_limit' => 'integer',
            'section_order' => 'array',
            'homepage_section_order' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'hero_enabled' => true,
            'category_blocks_enabled' => true,
            'new_arrivals_enabled' => true,
            'best_sellers_enabled' => true,
            'category_sections_enabled' => true,
            'all_products_enabled' => true,
            'homepage_hero_enabled' => true,
            'homepage_stats_enabled' => true,
            'homepage_best_sellers_enabled' => true,
            'homepage_testimonials_enabled' => true,
            'products_per_section' => 8,
            'all_products_per_page' => 12,
            'featured_categories_limit' => 4,
            'hero_autoplay' => true,
            'hero_autoplay_delay_ms' => 6000,
            'homepage_primary_cta_url' => '/shop',
            'homepage_secondary_cta_url' => '/how-it-works',
            'section_order' => [
                'hero',
                'categories',
                'new_arrivals',
                'best_sellers',
                'category_sections',
                'all_products',
            ],
            'homepage_section_order' => [
                'hero',
                'best_sellers',
                'stats',
                'testimonials',
                'trust',
            ],
        ]);
    }
}
