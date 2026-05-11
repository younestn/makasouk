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
        $nameEn = fake()->unique()->randomElement([
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

        $nameAr = fake()->randomElement([
            'الصدر',
            'الخصر',
            'الورك',
            'الكتف',
            'طول الكم',
            'محيط الرقبة',
            'الطول الداخلي للساق',
            'الطول',
            'طول الذراع',
            'طول التنورة',
            'محيط الرأس',
        ]);

        $audiences = fake()->randomElements(
            MeasurementOptions::selectableAudiences(),
            fake()->numberBetween(1, 3),
        );

        return [
            'name' => $nameEn,
            'name_en' => $nameEn,
            'name_ar' => $nameAr,
            'slug' => Str::slug($nameEn),
            'audience' => MeasurementOptions::legacyAudienceFromAudiences($audiences),
            'audiences' => $audiences,
            'description' => fake()->sentence(),
            'description_en' => fake()->sentence(),
            'description_ar' => 'قياس أساسي يستخدم أثناء طلبات الخياطة المخصصة.',
            'guide_text' => fake()->sentence(12),
            'guide_text_en' => fake()->sentence(12),
            'guide_text_ar' => 'استخدم شريط قياس مرنًا، وأبقِه مستقيمًا، ثم سجّل القيمة بالسنتيمتر.',
            'helper_text' => fake()->optional()->sentence(6),
            'helper_text_en' => fake()->optional()->sentence(6),
            'helper_text_ar' => 'أدخل القيمة بالسنتيمتر (سم).',
            'guide_image_path' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 30),
        ];
    }
}