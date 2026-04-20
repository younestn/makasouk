<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TailorActiveOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_tailor_active_orders_endpoint_returns_only_active_orders(): void
    {
        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);
        $otherTailor = User::factory()->tailor()->create(['approved_at' => now()]);

        Order::factory()->create([
            'tailor_id' => $tailor->id,
            'status' => Order::STATUS_ACCEPTED,
        ]);
        Order::factory()->create([
            'tailor_id' => $tailor->id,
            'status' => Order::STATUS_READY_FOR_DELIVERY,
        ]);
        Order::factory()->create([
            'tailor_id' => $tailor->id,
            'status' => Order::STATUS_COMPLETED,
        ]);
        Order::factory()->create([
            'tailor_id' => $otherTailor->id,
            'status' => Order::STATUS_PROCESSING,
        ]);

        $response = $this->actingAs($tailor, 'sanctum')
            ->getJson('/api/tailor/orders-active')
            ->assertOk()
            ->assertJsonPath('meta.scope', 'active');

        $this->assertCount(2, $response->json('data'));

        $statuses = collect($response->json('data'))->pluck('status')->all();
        $this->assertContains(Order::STATUS_ACCEPTED, $statuses);
        $this->assertContains(Order::STATUS_READY_FOR_DELIVERY, $statuses);
    }
}