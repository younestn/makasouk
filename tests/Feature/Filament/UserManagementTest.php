<?php

namespace Tests\Feature\Filament;

use App\Models\Category;
use App\Models\Product;
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

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/users/{$user->id}/suspend", ['reason' => 'policy breach'])
            ->assertOk();

        $this->assertTrue((bool) $user->fresh()->is_suspended);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/users/{$user->id}/unsuspend")
            ->assertOk();

        $this->assertFalse((bool) $user->fresh()->is_suspended);
    }

    public function test_admin_cannot_suspend_another_admin_account(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/users/{$otherAdmin->id}/suspend", ['reason' => 'test'])
            ->assertStatus(403);

        $this->assertFalse((bool) $otherAdmin->fresh()->is_suspended);
    }

    public function test_admin_can_browse_core_panel_resources(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by_admin_id' => $admin->id,
        ]);

        $this->actingAs($admin, 'web')->followingRedirects()->get('/admin-panel/users')->assertOk();
        $this->actingAs($admin, 'web')->followingRedirects()->get('/admin-panel/categories')->assertOk();
        $this->actingAs($admin, 'web')->followingRedirects()->get('/admin-panel/products')->assertOk();
        $this->actingAs($admin, 'web')->followingRedirects()->get('/admin-panel/orders')->assertOk();
        $this->actingAs($admin, 'web')->followingRedirects()->get('/admin-panel/reviews')->assertOk();

        $this->assertNotNull($product->id);
    }
}
