<?php

namespace Database\Factories;

use App\Models\Category;
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
            'description' => fake()->paragraph(),
            'pricing_type' => fake()->randomElement(['fixed', 'estimated']),
            'price' => fake()->randomFloat(2, 50, 500),
            'is_active' => true,
        ];
    }
}
