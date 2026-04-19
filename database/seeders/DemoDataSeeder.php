<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\TailorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::factory()->count(8)->create(['role' => User::ROLE_CUSTOMER]);
        $tailors = User::factory()->count(6)->tailor()->create();

        $categoryIds = Product::query()->pluck('category_id')->unique()->values();

        $tailors->each(function (User $tailor) use ($categoryIds): void {
            $categoryId = (int) $categoryIds->random();
            $lat = fake()->latitude(24, 35);
            $lng = fake()->longitude(-17, 55);

            $profile = TailorProfile::factory()->create([
                'user_id' => $tailor->id,
                'category_id' => $categoryId,
                'status' => TailorProfile::STATUS_ONLINE,
                'latitude' => $lat,
                'longitude' => $lng,
                'average_rating' => 0,
                'total_reviews' => 0,
            ]);

            DB::statement('UPDATE tailor_profiles SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?', [$lng, $lat, $profile->id]);
        });

        $products = Product::query()->get();

        $orders = Order::factory()->count(20)->make()->map(function (Order $order) use ($customers, $tailors, $products) {
            $product = $products->random();
            $tailor = $tailors->random();
            $status = fake()->randomElement([
                Order::STATUS_SEARCHING_FOR_TAILOR,
                Order::STATUS_ACCEPTED,
                Order::STATUS_PROCESSING,
                Order::STATUS_COMPLETED,
            ]);

            return [
                'customer_id' => $customers->random()->id,
                'tailor_id' => $status === Order::STATUS_SEARCHING_FOR_TAILOR ? null : $tailor->id,
                'product_id' => $product->id,
                'measurements' => $order->measurements,
                'delivery_latitude' => $order->delivery_latitude,
                'delivery_longitude' => $order->delivery_longitude,
                'status' => $status,
                'accepted_at' => in_array($status, [Order::STATUS_ACCEPTED, Order::STATUS_PROCESSING, Order::STATUS_COMPLETED], true) ? now()->subDays(rand(1, 5)) : null,
                'created_at' => now()->subDays(rand(1, 10)),
                'updated_at' => now(),
            ];
        })->all();

        Order::query()->insert($orders);

        Order::query()->each(function (Order $order): void {
            DB::statement('UPDATE orders SET delivery_location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?', [
                $order->delivery_longitude,
                $order->delivery_latitude,
                $order->id,
            ]);
        });

        $completedOrders = Order::query()->where('status', Order::STATUS_COMPLETED)->take(5)->get();

        foreach ($completedOrders as $completedOrder) {
            if ($completedOrder->tailor_id === null) {
                continue;
            }

            Review::query()->create([
                'order_id' => $completedOrder->id,
                'customer_id' => $completedOrder->customer_id,
                'tailor_id' => $completedOrder->tailor_id,
                'rating' => fake()->numberBetween(3, 5),
                'comment' => fake()->sentence(),
            ]);
        }

        TailorProfile::query()->each(function (TailorProfile $profile): void {
            $ratings = Review::query()->where('tailor_id', $profile->user_id);
            $count = $ratings->count();
            $avg = $count > 0 ? (float) $ratings->avg('rating') : 0;

            $profile->update([
                'total_reviews' => $count,
                'average_rating' => round($avg, 2),
            ]);
        });
    }
}
