<?php

namespace Tests\Feature;

use App\Models\Category;
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
        $product = Product::factory()->create(['category_id' => $category->id, 'created_by_admin_id' => $admin->id]);

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/customer/orders', [
                'product_id' => $product->id,
                'measurements' => ['height' => 170, 'waist' => 80],
                'customer_location' => ['latitude' => 33.5731, 'longitude' => -7.5898],
            ])
            ->assertCreated()
            ->assertJsonStructure(['status', 'order']);
    }
}