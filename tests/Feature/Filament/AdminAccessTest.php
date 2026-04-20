<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_panel(): void
    {
        $admin = User::factory()->admin()->create(['is_suspended' => false]);

        $this->actingAs($admin)->get('/admin-panel')->assertStatus(200);
    }

    public function test_non_admin_cannot_access_panel(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->actingAs($customer)->get('/admin-panel')->assertStatus(403);
    }

    public function test_suspended_admin_cannot_access_panel(): void
    {
        $admin = User::factory()->admin()->create(['is_suspended' => true]);

        $this->actingAs($admin)->get('/admin-panel')->assertStatus(403);
    }
}
