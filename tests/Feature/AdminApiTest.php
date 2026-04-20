<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users_and_pending_tailors(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->tailor()->create(['approved_at' => null]);

        $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users')->assertOk();
        $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users/pending-tailors')->assertOk();
    }

    public function test_non_admin_cannot_access_admin_endpoints(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->actingAs($customer, 'sanctum')->getJson('/api/admin/users')->assertStatus(403);
    }
}
