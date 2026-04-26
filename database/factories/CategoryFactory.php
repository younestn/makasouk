<?php

namespace Database\Factories;

use App\Models\Category;
use App\Support\Tailor\TailorOnboardingOptions;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'tailor_specialization' => fake()->randomElement(TailorOnboardingOptions::SPECIALIZATIONS),
            'description' => fake()->sentence(),
            'is_active' => true,
            'is_featured' => fake()->boolean(40),
            'sort_order' => fake()->numberBetween(0, 15),
        ];
    }
}
