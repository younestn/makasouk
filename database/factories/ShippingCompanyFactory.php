<?php

namespace Database\Factories;

use App\Models\ShippingCompany;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShippingCompany>
 */
class ShippingCompanyFactory extends Factory
{
    protected $model = ShippingCompany::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'name_en' => $name,
            'name_ar' => $name,
            'code' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 9999),
            'description' => fake()->sentence(),
            'description_en' => fake()->sentence(),
            'description_ar' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
