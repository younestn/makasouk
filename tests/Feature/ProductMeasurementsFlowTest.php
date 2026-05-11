<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Measurement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingCompany;
use App\Models\User;
use App\Support\Tailor\MeasurementOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductMeasurementsFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_order_requires_assigned_measurements_when_product_is_configured(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $shippingCompany = ShippingCompany::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by_admin_id' => $admin->id,
            'is_active' => true,
        ]);

        $chest = Measurement::factory()->create([
            'name' => 'Chest',
            'slug' => 'chest',
            'audience' => MeasurementOptions::AUDIENCE_UNISEX,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $waist = Measurement::factory()->create([
            'name' => 'Waist',
            'slug' => 'waist',
            'audience' => MeasurementOptions::AUDIENCE_UNISEX,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $product->measurements()->sync([$chest->id, $waist->id]);
        $fabricKey = 'library:'.$product->fabric_id;

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/customer/orders', [
                'product_id' => $product->id,
                'configuration' => [
                    'color' => 'ivory',
                    'fabric' => $fabricKey,
                ],
                'measurements' => [
                    'chest' => 94,
                ],
                'customer_location' => [
                    'latitude' => 36.7538,
                    'longitude' => 3.0588,
                    'work_wilaya' => 'Algiers',
                ],
                'shipping' => [
                    'company_id' => $shippingCompany->id,
                    'delivery_type' => 'office_pickup',
                    'commune' => 'Sidi M\'Hamed',
                    'neighborhood' => 'City Center',
                    'phone' => '+213555000001',
                    'email' => 'customer@example.com',
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['measurements.waist']);
    }

    public function test_customer_order_stores_only_selected_product_measurements(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $shippingCompany = ShippingCompany::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by_admin_id' => $admin->id,
            'is_active' => true,
        ]);
        $fabricKey = 'library:'.$product->fabric_id;

        $product->measurements()->sync(Measurement::factory()->count(2)->sequence(
            ['name' => 'Chest', 'slug' => 'chest', 'sort_order' => 1],
            ['name' => 'Waist', 'slug' => 'waist', 'sort_order' => 2],
        )->create()->pluck('id')->all());

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/customer/orders', [
                'product_id' => $product->id,
                'configuration' => [
                    'color' => 'ivory',
                    'fabric' => $fabricKey,
                ],
                'measurements' => [
                    'chest' => 96.4,
                    'waist' => 81.2,
                ],
                'customer_location' => [
                    'latitude' => 36.7538,
                    'longitude' => 3.0588,
                    'work_wilaya' => 'Algiers',
                ],
                'shipping' => [
                    'company_id' => $shippingCompany->id,
                    'delivery_type' => 'office_pickup',
                    'commune' => 'Sidi M\'Hamed',
                    'neighborhood' => 'City Center',
                    'phone' => '+213555000002',
                    'email' => 'customer@example.com',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.measurements.chest', 96.4)
            ->assertJsonPath('data.measurements.waist', 81.2);

        $order = Order::query()->findOrFail((int) $response->json('data.id'));

        $this->assertSame([
            'chest' => 96.4,
            'waist' => 81.2,
        ], $order->measurements);
    }

    public function test_measurement_can_match_multiple_audiences(): void
    {
        $measurement = Measurement::factory()->create([
            'name' => 'Height',
            'slug' => 'height',
            'audience' => MeasurementOptions::AUDIENCE_UNISEX,
            'audiences' => [
                MeasurementOptions::AUDIENCE_MEN,
                MeasurementOptions::AUDIENCE_WOMEN,
                MeasurementOptions::AUDIENCE_CHILDREN,
            ],
        ]);

        $menIds = Measurement::query()->forAudience(MeasurementOptions::AUDIENCE_MEN)->pluck('id')->all();
        $womenIds = Measurement::query()->forAudience(MeasurementOptions::AUDIENCE_WOMEN)->pluck('id')->all();
        $childrenIds = Measurement::query()->forAudience(MeasurementOptions::AUDIENCE_CHILDREN)->pluck('id')->all();

        $this->assertContains($measurement->id, $menIds);
        $this->assertContains($measurement->id, $womenIds);
        $this->assertContains($measurement->id, $childrenIds);
    }
}
