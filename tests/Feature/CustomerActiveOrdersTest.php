<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerActiveOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_active_orders_endpoint_returns_only_active_orders(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $otherCustomer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
        ]);
        Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => Order::STATUS_ACCEPTED,
        ]);
        Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => Order::STATUS_COMPLETED,
        ]);
        Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'status' => Order::STATUS_PROCESSING,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/customer/orders-active')
            ->assertOk()
            ->assertJsonPath('meta.scope', 'active');

        $this->assertCount(2, $response->json('data'));

        $statuses = collect($response->json('data'))->pluck('status')->all();
        $this->assertContains(Order::STATUS_SEARCHING_FOR_TAILOR, $statuses);
        $this->assertContains(Order::STATUS_ACCEPTED, $statuses);
    }
}