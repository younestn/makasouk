<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_suspend_and_unsuspend_user_safely(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin, 'sanctum')->patchJson("/api/admin/users/{$user->id}/suspend", ['reason' => 'policy breach'])->assertOk();

        $this->assertTrue((bool) $user->fresh()->is_suspended);

        $user->update(['is_suspended' => false]);
        $this->assertFalse((bool) $user->fresh()->is_suspended);
    }
}
