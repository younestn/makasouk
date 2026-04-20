<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastingAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_auth_private_channel(): void
    {
        $this->postJson('/broadcasting/auth', [
            'channel_name' => 'private-customer.1',
            'socket_id' => '123.456',
        ])->assertStatus(401);
    }

    public function test_customer_can_auth_own_channel(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->actingAs($customer, 'sanctum')
            ->postJson('/broadcasting/auth', [
                'channel_name' => "private-customer.{$customer->id}",
                'socket_id' => '123.456',
            ])
            ->assertOk();
    }
}
