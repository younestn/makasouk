<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TailorOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tailor_accepts_and_updates_status_sequence(): void
    {
        $tailor = User::factory()->tailor()->create();
        $order = Order::factory()->create(['status' => Order::STATUS_SEARCHING_FOR_TAILOR]);

        $this->actingAs($tailor, 'sanctum')
            ->postJson("/api/tailor/orders/{$order->id}/accept")
            ->assertOk();

        $order->refresh();

        $this->actingAs($tailor, 'sanctum')
            ->patchJson("/api/tailor/orders/{$order->id}/status", ['status' => Order::STATUS_PROCESSING])
            ->assertOk();
    }
}
