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
    'email' => 'customer1@makasouk.test',
    'password' => 'Password@123',
    'password_confirmation' => 'Password@123',
    'role' => User::ROLE_CUSTOMER,
]);

$register->assertCreated()->assertJsonStructure([
    'token',
    'user' => ['id', 'email'],
]);

$login = $this->postJson('/api/auth/login', [
    'email' => 'customer1@makasouk.test',
    'password' => 'Password@123',
]);
        $token = $login->assertOk()->json('token');

        $this->getJson('/api/auth/me', ['Authorization' => 'Bearer '.$token])
            ->assertOk();

        $this->postJson('/api/auth/logout', [], ['Authorization' => 'Bearer '.$token])
            ->assertOk();
    }
}
