<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Measurement;
use App\Models\Product;
use App\Models\User;
use App\Support\Tailor\MeasurementOptions;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', User::ROLE_ADMIN)->firstOrFail();

        Category::query()->get()->each(function (Category $category) use ($admin): void {
            $products = Product::factory()->count(3)->create([
                'category_id' => $category->id,
                'created_by_admin_id' => $admin->id,
            ]);

            $specialization = (string) $category->tailor_specialization;

            $audience = str_contains($specialization, 'Haute Couture')
                ? MeasurementOptions::AUDIENCE_WOMEN
                : ($specialization === 'Classique'
                    ? MeasurementOptions::AUDIENCE_MEN
                    : MeasurementOptions::AUDIENCE_UNISEX);

            $measurementIds = Measurement::query()
                ->active()
                ->forAudience($audience)
                ->ordered()
                ->limit(5)
                ->pluck('id')
                ->all();

            $products->each(function (Product $product) use ($measurementIds): void {
                if ($measurementIds === []) {
                    return;
                }

                $product->measurements()->sync($measurementIds);
            });
        });
    }
}
