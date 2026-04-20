<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TailorOrderShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_tailor_can_view_order_details(): void
    {
        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);

        $order = Order::factory()->create([
            'tailor_id' => $tailor->id,
            'status' => Order::STATUS_ACCEPTED,
        ]);

        $this->actingAs($tailor, 'sanctum')
            ->getJson("/api/tailor/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.tailor_id', $tailor->id);
    }

    public function test_tailor_cannot_view_order_assigned_to_another_tailor(): void
    {
        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);
        $otherTailor = User::factory()->tailor()->create(['approved_at' => now()]);

        $order = Order::factory()->create([
            'tailor_id' => $otherTailor->id,
            'status' => Order::STATUS_ACCEPTED,
        ]);

        $this->actingAs($tailor, 'sanctum')
            ->getJson("/api/tailor/orders/{$order->id}")
            ->assertForbidden();
    }
}
