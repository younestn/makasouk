<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Traditional Wear', 'slug' => 'traditional-wear'],
            ['name' => 'Dresses', 'slug' => 'dresses'],
            ['name' => 'Men Suits', 'slug' => 'men-suits'],
            ['name' => 'Kids Wear', 'slug' => 'kids-wear'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => 'Seeded category',
                    'is_active' => true,
                ],
            );
        }
    }
}
