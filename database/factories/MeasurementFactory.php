<?php

namespace Database\Factories;

use App\Models\Measurement;
use App\Support\Tailor\MeasurementOptions;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Measurement>
 */
class MeasurementFactory extends Factory
{
    protected $model = Measurement::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Chest',
            'Waist',
            'Hip',
            'Shoulder',
            'Sleeve Length',
            'Neck',
            'Inseam',
            'Height',
            'Arm Length',
            'Skirt Length',
            'Head Circumference',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'audience' => fake()->randomElement(MeasurementOptions::AUDIENCES),
            'description' => fake()->sentence(),
            'guide_text' => fake()->sentence(12),
            'helper_text' => fake()->optional()->sentence(6),
            'guide_image_path' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 30),
        ];
    }
}
