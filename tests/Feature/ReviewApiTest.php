<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_only_for_completed_orders(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tailor = User::factory()->tailor()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'tailor_id' => $tailor->id,
            'status' => Order::STATUS_ACCEPTED,
        ]);

        $this->actingAs($customer, 'sanctum')
            ->postJson("/api/customer/orders/{$order->id}/reviews", ['rating' => 5])
            ->assertStatus(422);
    }
}
