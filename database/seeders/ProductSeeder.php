<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', User::ROLE_ADMIN)->firstOrFail();

        Category::query()->get()->each(function (Category $category) use ($admin): void {
            Product::factory()->count(3)->create([
                'category_id' => $category->id,
                'created_by_admin_id' => $admin->id,
            ]);
        });
    }
}
