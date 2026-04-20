<?php

namespace Tests\Feature;

use App\Events\OrderAccepted;
use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealtimeEventPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_created_event_payload_contract(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
        ]);

        $event = new OrderCreated($order->fresh('product.category'), [$tailor->id], [$tailor->id => 4.25]);
        $payload = $event->broadcastWith();

        $this->assertSame('order.created', $payload['event']);
        $this->assertArrayHasKey('occurred_at', $payload);
        $this->assertArrayHasKey('order', $payload);
        $this->assertArrayHasKey('meta', $payload);
        $this->assertSame($order->id, $payload['order']['id']);
        $this->assertArrayNotHasKey('delivery_location', $payload['order']);
        $this->assertSame([$tailor->id], $payload['meta']['notified_tailor_ids']);
    }

    public function test_order_accepted_event_channels_include_customer_and_notified_tailors(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'tailor_id' => $tailor->id,
            'status' => Order::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        $event = new OrderAccepted($order->fresh(['customer', 'tailor', 'product']), $tailor->id, [$tailor->id]);

        $channelNames = collect($event->broadcastOn())->pluck('name')->all();

        $this->assertContains("private-tailor.{$tailor->id}", $channelNames);
        $this->assertContains("private-customer.{$customer->id}", $channelNames);

        $payload = $event->broadcastWith();
        $this->assertSame('order.accepted', $payload['event']);
        $this->assertSame($tailor->id, $payload['meta']['accepted_by_tailor_id']);
    }

    public function test_order_status_updated_event_payload_contract(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'tailor_id' => $tailor->id,
            'status' => Order::STATUS_PROCESSING,
        ]);

        $event = new OrderStatusUpdated($order->fresh(['customer', 'tailor', 'product']));
        $payload = $event->broadcastWith();

        $this->assertSame(['event', 'occurred_at', 'order'], array_keys($payload));
        $this->assertSame('order.status_updated', $payload['event']);
        $this->assertSame(Order::STATUS_PROCESSING, $payload['order']['status']);
    }
}