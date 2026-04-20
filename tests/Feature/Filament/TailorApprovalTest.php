<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TailorApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_browse_pending_tailors(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->tailor()->create(['approved_at' => null]);

        $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users/pending-tailors')->assertOk();
    }
}
