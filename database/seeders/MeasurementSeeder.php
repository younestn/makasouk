<?php

namespace Database\Seeders;

use App\Models\Measurement;
use App\Support\Tailor\MeasurementOptions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MeasurementSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['name' => 'Chest', 'audience' => MeasurementOptions::AUDIENCE_UNISEX, 'sort_order' => 1],
            ['name' => 'Waist', 'audience' => MeasurementOptions::AUDIENCE_UNISEX, 'sort_order' => 2],
            ['name' => 'Hip', 'audience' => MeasurementOptions::AUDIENCE_WOMEN, 'sort_order' => 3],
            ['name' => 'Shoulder', 'audience' => MeasurementOptions::AUDIENCE_UNISEX, 'sort_order' => 4],
            ['name' => 'Sleeve Length', 'audience' => MeasurementOptions::AUDIENCE_UNISEX, 'sort_order' => 5],
            ['name' => 'Neck', 'audience' => MeasurementOptions::AUDIENCE_MEN, 'sort_order' => 6],
            ['name' => 'Inseam', 'audience' => MeasurementOptions::AUDIENCE_MEN, 'sort_order' => 7],
            ['name' => 'Height', 'audience' => MeasurementOptions::AUDIENCE_UNISEX, 'sort_order' => 8],
            ['name' => 'Arm Length', 'audience' => MeasurementOptions::AUDIENCE_UNISEX, 'sort_order' => 9],
            ['name' => 'Skirt Length', 'audience' => MeasurementOptions::AUDIENCE_WOMEN, 'sort_order' => 10],
            ['name' => 'Head Circumference', 'audience' => MeasurementOptions::AUDIENCE_CHILDREN, 'sort_order' => 11],
        ];

        foreach ($definitions as $definition) {
            $slug = Str::slug($definition['name']);

            Measurement::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'audience' => $definition['audience'],
                    'description' => 'Standard measurement used during tailored order intake.',
                    'guide_text' => sprintf(
                        'Use a soft tape measure, keep it level, and record %s in centimeters.',
                        strtolower($definition['name']),
                    ),
                    'helper_text' => 'Enter value in centimeters (cm).',
                    'is_active' => true,
                    'sort_order' => $definition['sort_order'],
                ],
            );
        }
    }
}
