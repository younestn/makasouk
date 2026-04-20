<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\TailorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientAppFixturesSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()->updateOrCreate(
            ['email' => 'customer@makasouk.local'],
            [
                'name' => 'Seed Customer',
                'password' => Hash::make('Password@123'),
                'role' => User::ROLE_CUSTOMER,
                'approved_at' => now(),
                'is_suspended' => false,
            ],
        );

        $tailor = User::query()->updateOrCreate(
            ['email' => 'tailor@makasouk.local'],
            [
                'name' => 'Seed Tailor',
                'password' => Hash::make('Password@123'),
                'role' => User::ROLE_TAILOR,
                'approved_at' => now(),
                'is_suspended' => false,
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

        $latitude = 33.5731;
        $longitude = -7.5898;

        $tailorProfile = TailorProfile::query()->updateOrCreate(
            ['user_id' => $tailor->id],
            [
                'category_id' => $category->id,
                'status' => TailorProfile::STATUS_ONLINE,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'average_rating' => 4.8,
                'total_reviews' => 12,
            ],
        );

        DB::statement(
            'UPDATE tailor_profiles SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?',
            [$longitude, $latitude, $tailorProfile->id],
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
            'status' => Order::STATUS_ACCEPTED,
            'accepted_at' => now()->subHours(2),
        ]);

        DB::statement(
            'UPDATE orders SET delivery_location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?',
            [$longitude, $latitude, $order->id],
        );
    }
}
