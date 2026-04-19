<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_login_logout_flow(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Customer One',
            'email' => 'customer1@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'role' => User::ROLE_CUSTOMER,
        ]);

        $token = $register->assertCreated()->json('token');

        $this->getJson('/api/auth/me', ['Authorization' => 'Bearer '.$token])->assertOk();
        $this->postJson('/api/auth/logout', [], ['Authorization' => 'Bearer '.$token])->assertOk();
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'suspended@example.com',
            'password' => 'password',
            'is_suspended' => true,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'suspended@example.com',
            'password' => 'password',
        ])->assertStatus(403);
    }
}
