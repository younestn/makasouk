<?php

namespace Tests\Feature\Filament;

use App\Models\Category;
use App\Models\TailorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TailorApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_pending_tailor(): void
    {
        $admin = User::factory()->admin()->create();
        $tailor = User::factory()->tailor()->create(['approved_at' => null]);
        $category = Category::factory()->create();

        TailorProfile::factory()->create([
            'user_id' => $tailor->id,
            'category_id' => $category->id,
            'status' => TailorProfile::STATUS_ONLINE,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/users/{$tailor->id}/approve-tailor")
            ->assertOk();

        $tailor = $tailor->fresh('tailorProfile');

        $this->assertNotNull($tailor->approved_at);
        $this->assertSame(TailorProfile::STATUS_OFFLINE, $tailor->tailorProfile?->status);
    }

    public function test_non_admin_cannot_approve_pending_tailor(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tailor = User::factory()->tailor()->create(['approved_at' => null]);

        $this->actingAs($customer, 'sanctum')
            ->patchJson("/api/admin/users/{$tailor->id}/approve-tailor")
            ->assertStatus(403);
    }
}