<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_login_page_renders_for_guest(): void
    {
        $this->get('/admin-panel/login')->assertOk();
    }

    public function test_admin_can_access_panel_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['is_suspended' => false]);

        $this->actingAs($admin, 'web')->followingRedirects()->get('/admin-panel')->assertOk();
    }

    public function test_customer_cannot_access_panel(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->actingAs($customer, 'web')->get('/admin-panel')->assertForbidden();
    }

    public function test_tailor_cannot_access_panel(): void
    {
        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);

        $this->actingAs($tailor, 'web')->get('/admin-panel')->assertForbidden();
    }

    public function test_suspended_admin_cannot_access_panel(): void
    {
        $admin = User::factory()->admin()->create(['is_suspended' => true]);

        $this->actingAs($admin, 'web')->get('/admin-panel')->assertForbidden();
    }
}
