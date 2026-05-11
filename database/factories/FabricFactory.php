<?php

namespace Database\Factories;

use App\Models\Fabric;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Fabric>
 */
class FabricFactory extends Factory
{
    protected $model = Fabric::class;

    public function definition(): array
    {
        $baseName = fake()->randomElement([
            'Cotton Premium',
            'Silk Satin',
            'Linen Blend',
            'Wool Crepe',
            'Traditional Brocade',
            'Velvet Soft',
            'Chiffon Light',
            'Twill Classic',
            'Jacquard Royal',
            'Viscose Comfort',
        ]);

        $nameEn = $baseName.' '.fake()->numberBetween(100, 9999);
        $nameAr = fake()->randomElement([
            'قطن فاخر',
            'حرير ساتان',
            'مزيج كتان',
            'كريب صوفي',
            'بروكار تقليدي',
        ]).' '.fake()->numberBetween(100, 9999);

        return [
            'name' => $nameEn,
            'name_en' => $nameEn,
            'name_ar' => $nameAr,
            'slug' => Str::slug($nameEn),
            'country' => fake()->randomElement([
                'Algeria',
                'Morocco',
                'Turkey',
                'Italy',
                'France',
                'Egypt',
                'Tunisia',
                'India',
                'China',
            ]),
            'description' => fake()->optional()->sentence(12),
            'description_en' => fake()->optional()->sentence(12),
            'description_ar' => 'مرجع قماش مناسب للمنتجات المفصلة حسب الطلب.',
            'image_path' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
            'notes' => fake()->optional()->sentence(8),
        ];
    }
}