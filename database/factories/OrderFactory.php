<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'tailor_id' => null,
            'product_id' => Product::factory(),
            'measurements' => [
                'height' => fake()->numberBetween(140, 200),
                'chest' => fake()->numberBetween(70, 120),
                'waist' => fake()->numberBetween(60, 110),
            ],
            'delivery_latitude' => fake()->latitude(19, 37),
            'delivery_longitude' => fake()->longitude(-8, 12),
            'delivery_work_wilaya' => null,
            'delivery_location_label' => null,
            'delivery_location' => null,
            'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
            'matched_specialization' => null,
            'matching_snapshot' => null,
            'accepted_at' => null,
        ];
    }
}
