<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Fabric;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = ucfirst(fake()->words(3, true));

        return [
            'category_id' => Category::factory(),
            'created_by_admin_id' => User::factory()->admin(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 9999),
            'short_description' => fake()->sentence(12),
            'description' => fake()->paragraph(),
            'fabric_id' => Fabric::factory(),
            'fabric_type' => fake()->randomElement(['Cotton', 'Silk', 'Linen', 'Wool Blend']),
            'fabric_country' => fake()->randomElement(['Algeria', 'Morocco', 'Turkey', 'Italy']),
            'fabric_description' => fake()->boolean(60) ? fake()->sentence(10) : null,
            'fabric_image_path' => null,
            'pricing_type' => fake()->randomElement(['fixed', 'estimated']),
            'price' => fake()->randomFloat(2, 50, 500),
            'sale_price' => fake()->boolean(30) ? fake()->randomFloat(2, 30, 450) : null,
            'stock' => fake()->numberBetween(1, 60),
            'sku' => 'SKU-'.strtoupper(fake()->bothify('???-#####')),
            'is_active' => true,
            'is_featured' => fake()->boolean(30),
            'is_best_seller' => fake()->boolean(20),
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
