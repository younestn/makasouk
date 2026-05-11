<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingCompany;
use App\Models\TailorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderMatchingRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_order_is_routed_to_matching_specialization_and_nearest_tailor(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $admin = User::factory()->admin()->create();
        $shippingCompany = ShippingCompany::factory()->create();

        $targetCategory = Category::factory()->create([
            'tailor_specialization' => 'Traditionnel',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $targetCategory->id,
            'created_by_admin_id' => $admin->id,
            'is_active' => true,
        ]);

        $nearTailor = User::factory()->tailor()->create(['approved_at' => now()]);
        $farTailor = User::factory()->tailor()->create(['approved_at' => now()]);
        $wrongSpecializationTailor = User::factory()->tailor()->create(['approved_at' => now()]);

        TailorProfile::factory()->create([
            'user_id' => $nearTailor->id,
            'category_id' => $targetCategory->id,
            'specialization' => 'Traditionnel',
            'work_wilaya' => 'Algiers',
            'status' => TailorProfile::STATUS_ONLINE,
            'latitude' => 36.7538,
            'longitude' => 3.0588,
        ]);

        TailorProfile::factory()->create([
            'user_id' => $farTailor->id,
            'category_id' => $targetCategory->id,
            'specialization' => 'Traditionnel',
            'work_wilaya' => 'Algiers',
            'status' => TailorProfile::STATUS_ONLINE,
            'latitude' => 35.6971,
            'longitude' => -0.6308,
        ]);

        TailorProfile::factory()->create([
            'user_id' => $wrongSpecializationTailor->id,
            'category_id' => $targetCategory->id,
            'specialization' => 'Moderne',
            'work_wilaya' => 'Algiers',
            'status' => TailorProfile::STATUS_ONLINE,
            'latitude' => 36.7538,
            'longitude' => 3.0588,
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/customer/orders', [
                'product_id' => $product->id,
                'measurements' => ['height' => 170, 'waist' => 80],
                'customer_location' => [
                    'latitude' => 36.7538,
                    'longitude' => 3.0588,
                    'work_wilaya' => 'Algiers',
                    'label' => 'Algiers center',
                ],
                'shipping' => [
                    'company_id' => $shippingCompany->id,
                    'delivery_type' => 'office_pickup',
                    'commune' => 'Sidi M\'Hamed',
                    'neighborhood' => 'City Center',
                    'phone' => '+213555000004',
                    'email' => 'customer@example.com',
                ],
            ])
            ->assertCreated();

        $response
            ->assertJsonPath('meta.matched_tailors_count', 2)
            ->assertJsonPath('meta.matched_specialization', 'Traditionnel')
            ->assertJsonPath('meta.recommended_tailor_id', $nearTailor->id)
            ->assertJsonPath('data.matched_specialization', 'Traditionnel');

        $orderId = (int) $response->json('data.id');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'matched_specialization' => 'Traditionnel',
            'delivery_work_wilaya' => 'Algiers',
        ]);
    }

    public function test_tailor_cannot_accept_order_when_specialization_does_not_match(): void
    {
        $category = Category::factory()->create([
            'tailor_specialization' => 'Traditionnel',
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);

        TailorProfile::factory()->create([
            'user_id' => $tailor->id,
            'category_id' => $category->id,
            'specialization' => 'Moderne',
            'status' => TailorProfile::STATUS_ONLINE,
        ]);

        $order = Order::factory()->create([
            'product_id' => $product->id,
            'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
        ]);

        $this->actingAs($tailor, 'sanctum')
            ->postJson("/api/tailor/orders/{$order->id}/accept")
            ->assertStatus(422)
            ->assertJsonPath('message', 'This order is not eligible for your specialization or availability profile.');
    }

    public function test_tailor_can_update_saved_location_once_and_reuse_it(): void
    {
        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);

        TailorProfile::factory()->create([
            'user_id' => $tailor->id,
            'status' => TailorProfile::STATUS_OFFLINE,
            'work_wilaya' => 'Oran',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($tailor, 'sanctum')
            ->patchJson('/api/tailor/profile/location', [
                'latitude' => 31.6295,
                'longitude' => -2.2100,
                'work_wilaya' => 'Oran',
            ])
            ->assertOk();

        $response
            ->assertJsonPath('data.latitude', 31.6295)
            ->assertJsonPath('data.longitude', -2.21)
            ->assertJsonPath('data.work_wilaya', 'Oran');

        $this->assertDatabaseHas('tailor_profiles', [
            'user_id' => $tailor->id,
            'work_wilaya' => 'Oran',
            'latitude' => 31.6295,
            'longitude' => -2.2100,
        ]);
    }
}
