<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class BroadcastingAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'makasouk-test-key',
            'broadcasting.connections.reverb.secret' => 'makasouk-test-secret',
            'broadcasting.connections.reverb.app_id' => 'makasouk-test-app',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1',
            'broadcasting.connections.reverb.options.port' => 8080,
            'broadcasting.connections.reverb.options.scheme' => 'http',
            'broadcasting.connections.reverb.options.useTLS' => false,
        ]);

        app(BroadcastManager::class)->forgetDrivers();

        Broadcast::channel('tailor.{id}', function ($user, int $id): bool {
            return $user->role === User::ROLE_TAILOR
                && $user->id === $id
                && ! $user->is_suspended
                && $user->approved_at !== null;
        });

        Broadcast::channel('customer.{id}', function ($user, int $id): bool {
            return $user->role === User::ROLE_CUSTOMER
                && $user->id === $id
                && ! $user->is_suspended;
        });

        Broadcast::channel('admin.{id}', function ($user, int $id): bool {
            return $user->role === User::ROLE_ADMIN
                && $user->id === $id
                && ! $user->is_suspended;
        });
    }

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

    public function test_customer_cannot_auth_another_customer_channel(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $otherCustomer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->actingAs($customer, 'sanctum')
            ->postJson('/broadcasting/auth', [
                'channel_name' => "private-customer.{$otherCustomer->id}",
                'socket_id' => '123.456',
            ])
            ->assertStatus(403);
    }

    public function test_suspended_user_cannot_auth_private_channel(): void
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'is_suspended' => true,
        ]);

        $this->actingAs($customer, 'sanctum')
            ->postJson('/broadcasting/auth', [
                'channel_name' => "private-customer.{$customer->id}",
                'socket_id' => '123.456',
            ])
            ->assertStatus(403);
    }

    public function test_unapproved_tailor_cannot_auth_tailor_channel(): void
    {
        $tailor = User::factory()->tailor()->create(['approved_at' => null]);

        $this->actingAs($tailor, 'sanctum')
            ->postJson('/broadcasting/auth', [
                'channel_name' => "private-tailor.{$tailor->id}",
                'socket_id' => '123.456',
            ])
            ->assertStatus(403);
    }

    public function test_approved_tailor_can_auth_own_channel(): void
    {
        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);

        $this->actingAs($tailor, 'sanctum')
            ->postJson('/broadcasting/auth', [
                'channel_name' => "private-tailor.{$tailor->id}",
                'socket_id' => '123.456',
            ])
            ->assertOk();
    }
}
