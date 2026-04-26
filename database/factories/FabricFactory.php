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

        $name = $baseName . ' ' . fake()->numberBetween(100, 9999);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
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
            'image_path' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
            'notes' => fake()->optional()->sentence(8),
        ];
    }
}