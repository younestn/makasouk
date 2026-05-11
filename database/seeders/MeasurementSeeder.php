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
            ['name_en' => 'Chest', 'name_ar' => 'الصدر', 'audiences' => MeasurementOptions::selectableAudiences(), 'sort_order' => 1],
            ['name_en' => 'Waist', 'name_ar' => 'الخصر', 'audiences' => MeasurementOptions::selectableAudiences(), 'sort_order' => 2],
            ['name_en' => 'Hip', 'name_ar' => 'الورك', 'audiences' => [MeasurementOptions::AUDIENCE_WOMEN], 'sort_order' => 3],
            ['name_en' => 'Shoulder', 'name_ar' => 'الكتف', 'audiences' => MeasurementOptions::selectableAudiences(), 'sort_order' => 4],
            ['name_en' => 'Sleeve Length', 'name_ar' => 'طول الكم', 'audiences' => MeasurementOptions::selectableAudiences(), 'sort_order' => 5],
            ['name_en' => 'Neck', 'name_ar' => 'محيط الرقبة', 'audiences' => [MeasurementOptions::AUDIENCE_MEN], 'sort_order' => 6],
            ['name_en' => 'Inseam', 'name_ar' => 'الطول الداخلي للساق', 'audiences' => [MeasurementOptions::AUDIENCE_MEN], 'sort_order' => 7],
            ['name_en' => 'Height', 'name_ar' => 'الطول', 'audiences' => MeasurementOptions::selectableAudiences(), 'sort_order' => 8],
            ['name_en' => 'Arm Length', 'name_ar' => 'طول الذراع', 'audiences' => MeasurementOptions::selectableAudiences(), 'sort_order' => 9],
            ['name_en' => 'Skirt Length', 'name_ar' => 'طول التنورة', 'audiences' => [MeasurementOptions::AUDIENCE_WOMEN], 'sort_order' => 10],
            ['name_en' => 'Head Circumference', 'name_ar' => 'محيط الرأس', 'audiences' => [MeasurementOptions::AUDIENCE_CHILDREN], 'sort_order' => 11],
        ];

        foreach ($definitions as $definition) {
            $slug = Str::slug($definition['name_en']);

            Measurement::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name_en'],
                    'name_en' => $definition['name_en'],
                    'name_ar' => $definition['name_ar'],
                    'audience' => MeasurementOptions::legacyAudienceFromAudiences($definition['audiences']),
                    'audiences' => $definition['audiences'],
                    'description' => 'Standard measurement used during tailored order intake.',
                    'description_en' => 'Standard measurement used during tailored order intake.',
                    'description_ar' => 'قياس أساسي يُستخدم أثناء استقبال طلبات الخياطة المخصصة.',
                    'guide_text' => sprintf(
                        'Use a soft tape measure, keep it level, and record %s in centimeters.',
                        strtolower($definition['name_en']),
                    ),
                    'guide_text_en' => sprintf(
                        'Use a soft tape measure, keep it level, and record %s in centimeters.',
                        strtolower($definition['name_en']),
                    ),
                    'guide_text_ar' => 'استخدم شريط قياس مرنًا، وأبقِه مستقيمًا، ثم سجّل القياس بالسنتيمتر.',
                    'helper_text' => 'Enter value in centimeters (cm).',
                    'helper_text_en' => 'Enter value in centimeters (cm).',
                    'helper_text_ar' => 'أدخل القيمة بالسنتيمتر (سم).',
                    'is_active' => true,
                    'sort_order' => $definition['sort_order'],
                ],
            );
        }
    }
}