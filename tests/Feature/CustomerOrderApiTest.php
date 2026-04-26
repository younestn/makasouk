<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_order_and_no_tailor_case_is_handled(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $category = Category::factory()->create();
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by_admin_id' => $admin->id,
            'is_active' => true,
        ]);

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/customer/orders', [
                'product_id' => $product->id,
                'measurements' => ['height' => 170, 'waist' => 80],
                'customer_location' => ['latitude' => 36.7538, 'longitude' => 3.0588],
            ])
            ->assertCreated()
            ->assertJsonStructure(['message', 'data', 'meta']);
    }

    public function test_customer_can_cancel_order_in_allowed_status(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
        ]);

        $this->actingAs($customer, 'sanctum')
            ->patchJson("/api/customer/orders/{$order->id}/cancel", ['reason' => 'changed mind'])
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_CANCELLED_BY_CUSTOMER);
    }
}
