<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\TailorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientAppFixturesSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()->updateOrCreate(
            ['email' => 'customer@makasouk.local'],
            [
                'name' => 'Seed Customer',
                'phone' => '+213550000001',
                'password' => Hash::make('Password@123'),
                'role' => User::ROLE_CUSTOMER,
                'approved_at' => now(),
                'is_suspended' => false,
                'phone_verified_at' => now(),
            ],
        );

        $tailor = User::query()->updateOrCreate(
            ['email' => 'tailor@makasouk.local'],
            [
                'name' => 'Seed Tailor',
                'phone' => '+213550000002',
                'password' => Hash::make('Password@123'),
                'role' => User::ROLE_TAILOR,
                'approved_at' => now(),
                'is_suspended' => false,
                'phone_verified_at' => now(),
            ],
        );

        $category = Category::query()->where('is_active', true)->first() ?? Category::query()->first();
        $product = Product::query()
            ->when($category !== null, fn ($query) => $query->where('category_id', $category->id))
            ->where('is_active', true)
            ->first() ?? Product::query()->first();

        if ($category === null || $product === null) {
            return;
        }

        if (! filled($category->tailor_specialization)) {
            $category->forceFill(['tailor_specialization' => 'Traditionnel'])->save();
        }

        $latitude = 36.7538;
        $longitude = 3.0588;

        $tailorProfile = TailorProfile::query()->updateOrCreate(
            ['user_id' => $tailor->id],
            [
                'category_id' => $category->id,
                'specialization' => 'Traditionnel',
                'work_wilaya' => 'Algiers',
                'years_of_experience' => 8,
                'gender' => 'male',
                'workers_count' => 4,
                'status' => TailorProfile::STATUS_ONLINE,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'average_rating' => 4.8,
                'total_reviews' => 12,
            ],
        );
        Order::query()
            ->where('customer_id', $customer->id)
            ->where('tailor_id', $tailor->id)
            ->where('status', Order::STATUS_ACCEPTED)
            ->delete();

        $order = Order::query()->create([
            'customer_id' => $customer->id,
            'tailor_id' => $tailor->id,
            'product_id' => $product->id,
            'measurements' => [
                'height' => 170,
                'waist' => 80,
                'seed_reference' => 'phase8_launch_readiness',
            ],
            'delivery_latitude' => $latitude,
            'delivery_longitude' => $longitude,
            'delivery_work_wilaya' => 'Algiers',
            'delivery_location_label' => 'Casablanca city center',
            'status' => Order::STATUS_ACCEPTED,
            'matched_specialization' => $category->tailor_specialization,
            'matching_snapshot' => [
                'strategy' => 'seed_fixture',
                'eligible_tailor_ids' => [$tailor->id],
                'recommended_tailor_id' => $tailor->id,
            ],
            'accepted_at' => now()->subHours(2),
        ]);
    }
}
