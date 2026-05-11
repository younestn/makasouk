<?php

namespace Database\Factories;

use App\Models\Category;
use App\Support\Tailor\TailorOnboardingOptions;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $nameEn = ucfirst(fake()->unique()->words(2, true));
        $nameAr = fake()->randomElement([
            'قفطان راقٍ',
            'جلابة عصرية',
            'أزياء سهرة',
            'خياطة كلاسيكية',
            'ملابس يومية',
        ]).' '.fake()->unique()->numberBetween(10, 999);

        return [
            'name' => $nameEn,
            'name_en' => $nameEn,
            'name_ar' => $nameAr,
            'slug' => Str::slug($nameEn),
            'tailor_specialization' => fake()->randomElement(TailorOnboardingOptions::SPECIALIZATIONS),
            'description' => fake()->sentence(),
            'description_en' => fake()->sentence(),
            'description_ar' => 'تصنيف متجر معروض للعملاء والخياطين.',
            'is_active' => true,
            'is_featured' => fake()->boolean(40),
            'sort_order' => fake()->numberBetween(0, 15),
        ];
    }
}