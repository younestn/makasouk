<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Measurement;
use App\Models\Order;
use App\Models\Product;
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

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/customer/orders', [
                'product_id' => $product->id,
                'measurements' => [
                    'chest' => 94,
                ],
                'customer_location' => [
                    'latitude' => 36.7538,
                    'longitude' => 3.0588,
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

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by_admin_id' => $admin->id,
            'is_active' => true,
        ]);

        $product->measurements()->sync(Measurement::factory()->count(2)->sequence(
            ['name' => 'Chest', 'slug' => 'chest', 'sort_order' => 1],
            ['name' => 'Waist', 'slug' => 'waist', 'sort_order' => 2],
        )->create()->pluck('id')->all());

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/customer/orders', [
                'product_id' => $product->id,
                'measurements' => [
                    'chest' => 96.4,
                    'waist' => 81.2,
                ],
                'customer_location' => [
                    'latitude' => 36.7538,
                    'longitude' => 3.0588,
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
}
