<?php

namespace Database\Seeders;

use App\Models\ShopBanner;
use App\Models\ShopSetting;
use Illuminate\Database\Seeder;

class ShopContentSeeder extends Seeder
{
    public function run(): void
    {
        ShopSetting::current();

        $banners = [
            [
                'title' => 'Spring Tailoring Deals',
                'subtitle' => 'Book your custom outfit with priority tailoring slots.',
                'badge' => 'Limited Offer',
                'image_path' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=1400&q=80',
                'button_text' => 'Shop Collection',
                'button_link' => '/shop',
                'placement' => 'shop_hero',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Featured Makers of the Week',
                'subtitle' => 'Discover products and services from top-rated tailors.',
                'badge' => 'Top Rated',
                'image_path' => 'https://images.unsplash.com/photo-1593032528885-8b08058a2b8f?auto=format&fit=crop&w=1400&q=80',
                'button_text' => 'Discover More',
                'button_link' => '/shop?featured=1',
                'placement' => 'shop_hero',
                'display_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            ShopBanner::query()->updateOrCreate(
                ['title' => $banner['title'], 'placement' => $banner['placement']],
                $banner,
            );
        }
    }
}
