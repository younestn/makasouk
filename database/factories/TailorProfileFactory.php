<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\TailorProfile;
use App\Models\User;
use App\Support\Tailor\TailorOnboardingOptions;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TailorProfile>
 */
class TailorProfileFactory extends Factory
{
    protected $model = TailorProfile::class;

    public function definition(): array
    {
        $lat = fake()->latitude(19, 37);
        $lng = fake()->longitude(-8, 12);

        return [
            'user_id' => User::factory()->tailor(),
            'category_id' => Category::factory(),
            'specialization' => function (array $attributes): string {
                $categorySpecialization = Category::query()
                    ->whereKey($attributes['category_id'] ?? null)
                    ->value('tailor_specialization');

                return $categorySpecialization ?: fake()->randomElement(TailorOnboardingOptions::SPECIALIZATIONS);
            },
            'work_wilaya' => fake()->randomElement(TailorOnboardingOptions::WILAYAS),
            'years_of_experience' => fake()->numberBetween(1, 25),
            'gender' => fake()->randomElement(array_keys(TailorOnboardingOptions::genderOptions())),
            'workers_count' => fake()->numberBetween(1, 20),
            'status' => fake()->randomElement([TailorProfile::STATUS_ONLINE, TailorProfile::STATUS_OFFLINE]),
            'average_rating' => fake()->randomFloat(2, 3, 5),
            'total_reviews' => fake()->numberBetween(0, 50),
            'latitude' => $lat,
            'longitude' => $lng,
        ];
    }
}
