<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name_en' => 'Traditional Wear', 'name_ar' => 'ملابس تقليدية', 'slug' => 'traditional-wear', 'tailor_specialization' => 'Traditionnel'],
            ['name_en' => 'Dresses', 'name_ar' => 'فساتين', 'slug' => 'dresses', 'tailor_specialization' => 'Haute Couture / Soiré'],
            ['name_en' => 'Men Suits', 'name_ar' => 'بدلات رجالية', 'slug' => 'men-suits', 'tailor_specialization' => 'Classique'],
            ['name_en' => 'Kids Wear', 'name_ar' => 'ملابس أطفال', 'slug' => 'kids-wear', 'tailor_specialization' => 'Regular sewing'],
        ];

        foreach ($categories as $index => $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name_en'],
                    'name_en' => $category['name_en'],
                    'name_ar' => $category['name_ar'],
                    'tailor_specialization' => $category['tailor_specialization'],
                    'description' => 'Seeded category',
                    'description_en' => 'Seeded category',
                    'description_ar' => 'تصنيف تجريبي للمتجر.',
                    'is_active' => true,
                    'is_featured' => $index < 3,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}