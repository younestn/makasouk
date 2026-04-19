<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\TailorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TailorProfile>
 */
class TailorProfileFactory extends Factory
{
    protected $model = TailorProfile::class;

    public function definition(): array
    {
        $lat = fake()->latitude(24, 35);
        $lng = fake()->longitude(-17, 55);

        return [
            'user_id' => User::factory()->tailor(),
            'category_id' => Category::factory(),
            'status' => fake()->randomElement([TailorProfile::STATUS_ONLINE, TailorProfile::STATUS_OFFLINE]),
            'average_rating' => fake()->randomFloat(2, 3, 5),
            'total_reviews' => fake()->numberBetween(0, 50),
            'latitude' => $lat,
            'longitude' => $lng,
            'location' => null,
        ];
    }
}
