<?php

namespace Tests\Feature\Filament;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\TailorProfile;
use App\Models\User;
use App\Support\Tailor\TailorOnboardingOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderMatchingReviewPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_order_matching_review_page(): void
    {
        $specialization = TailorOnboardingOptions::SPECIALIZATIONS[0];
        $wilaya = TailorOnboardingOptions::WILAYAS[0];

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $category = Category::factory()->create([
            'tailor_specialization' => $specialization,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by_admin_id' => $admin->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
            'delivery_work_wilaya' => $wilaya,
            'delivery_latitude' => 36.7525,
            'delivery_longitude' => 3.04197,
        ]);

        $tailor = User::factory()->tailor()->create([
            'approved_at' => now(),
            'phone' => '+213555000111',
            'phone_verified_at' => now(),
            'is_suspended' => false,
        ]);

        TailorProfile::factory()->for($tailor)->create([
            'category_id' => $category->id,
            'specialization' => $specialization,
            'work_wilaya' => $wilaya,
            'status' => TailorProfile::STATUS_ONLINE,
            'latitude' => 36.7530,
            'longitude' => 3.0425,
        ]);

        $this->actingAs($admin, 'web')
            ->followingRedirects()
            ->get("/admin-panel/orders/{$order->id}/matching-review")
            ->assertOk()
            ->assertSee('Order Context')
            ->assertSee('Recommended Tailor')
            ->assertSee($tailor->name);
    }
}
