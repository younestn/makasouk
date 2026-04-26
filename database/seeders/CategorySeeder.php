<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Traditional Wear', 'slug' => 'traditional-wear', 'tailor_specialization' => 'Traditionnel'],
            ['name' => 'Dresses', 'slug' => 'dresses', 'tailor_specialization' => 'Haute Couture / Soir'."\u{00E9}"],
            ['name' => 'Men Suits', 'slug' => 'men-suits', 'tailor_specialization' => 'Classique'],
            ['name' => 'Kids Wear', 'slug' => 'kids-wear', 'tailor_specialization' => 'Regular sewing'],
        ];

        foreach ($categories as $index => $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'tailor_specialization' => $category['tailor_specialization'],
                    'description' => 'Seeded category',
                    'is_active' => true,
                    'is_featured' => $index < 3,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
