<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\TailorProfile;
use App\Models\TailorOrderOffer;
use App\Services\TailorScoringService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TailorOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tailor_accepts_and_updates_status_sequence(): void
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
            'specialization' => 'Traditionnel',
            'status' => TailorProfile::STATUS_ONLINE,
        ]);

        $order = Order::factory()->create([
            'product_id' => $product->id,
            'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
        ]);

        $this->actingAs($tailor, 'sanctum')->postJson("/api/tailor/orders/{$order->id}/accept")->assertOk();

        $order->refresh();

        $this->actingAs($tailor, 'sanctum')
            ->patchJson("/api/tailor/orders/{$order->id}/status", ['status' => Order::STATUS_PROCESSING])
            ->assertOk();

        $this->actingAs($tailor, 'sanctum')
            ->patchJson("/api/tailor/orders/{$order->id}/status", ['status' => Order::STATUS_COMPLETED])
            ->assertStatus(422);
    }

    public function test_tailor_offer_can_be_read_and_declined_with_score_event(): void
    {
        $category = Category::factory()->create([
            'tailor_specialization' => 'Traditionnel',
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 12000,
        ]);

        $tailor = User::factory()->tailor()->create(['approved_at' => now()]);

        TailorProfile::factory()->create([
            'user_id' => $tailor->id,
            'category_id' => $category->id,
            'specialization' => 'Traditionnel',
            'status' => TailorProfile::STATUS_ONLINE,
            'score' => 100,
        ]);

        $order = Order::factory()->create([
            'product_id' => $product->id,
            'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
        ]);

        $offer = TailorOrderOffer::query()->create([
            'order_id' => $order->id,
            'tailor_id' => $tailor->id,
            'status' => TailorOrderOffer::STATUS_UNREAD,
        ]);

        $this->actingAs($tailor, 'sanctum')
            ->getJson('/api/tailor/orders-offers')
            ->assertOk()
            ->assertJsonPath('meta.unread_count', 1)
            ->assertJsonPath('data.0.tailor_offer.is_unread', true);

        $this->actingAs($tailor, 'sanctum')
            ->getJson("/api/tailor/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.tailor_offer.status', TailorOrderOffer::STATUS_READ);

        $offer->refresh();

        $this->assertSame(TailorOrderOffer::STATUS_READ, $offer->status);
        $this->assertNotNull($offer->read_at);

        $this->actingAs($tailor, 'sanctum')
            ->postJson("/api/tailor/orders/{$order->id}/decline", [
                'reason' => TailorOrderOffer::REASON_UNAVAILABLE,
                'note' => 'Not available today.',
            ])
            ->assertOk()
            ->assertJsonPath('data.tailor_offer.status', TailorOrderOffer::STATUS_REJECTED);

        $offer->refresh();
        $tailor->tailorProfile->refresh();

        $this->assertSame(TailorOrderOffer::STATUS_REJECTED, $offer->status);
        $this->assertSame(98, $tailor->tailorProfile->score);
        $this->assertDatabaseHas('tailor_score_events', [
            'tailor_id' => $tailor->id,
            'order_id' => $order->id,
            'event' => TailorScoringService::EVENT_REJECTED,
            'delta' => -2,
            'score_after' => 98,
        ]);
    }

    public function test_only_one_tailor_can_accept_an_offered_order(): void
    {
        $category = Category::factory()->create([
            'tailor_specialization' => 'Traditionnel',
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $firstTailor = User::factory()->tailor()->create(['approved_at' => now()]);
        $secondTailor = User::factory()->tailor()->create(['approved_at' => now()]);

        foreach ([$firstTailor, $secondTailor] as $tailor) {
            TailorProfile::factory()->create([
                'user_id' => $tailor->id,
                'category_id' => $category->id,
                'specialization' => 'Traditionnel',
                'status' => TailorProfile::STATUS_ONLINE,
            ]);
        }

        $order = Order::factory()->create([
            'product_id' => $product->id,
            'status' => Order::STATUS_SEARCHING_FOR_TAILOR,
        ]);

        foreach ([$firstTailor, $secondTailor] as $tailor) {
            TailorOrderOffer::query()->create([
                'order_id' => $order->id,
                'tailor_id' => $tailor->id,
                'status' => TailorOrderOffer::STATUS_UNREAD,
            ]);
        }

        $this->actingAs($firstTailor, 'sanctum')
            ->postJson("/api/tailor/orders/{$order->id}/accept")
            ->assertOk();

        $this->actingAs($secondTailor, 'sanctum')
            ->postJson("/api/tailor/orders/{$order->id}/accept")
            ->assertStatus(409);

        $order->refresh();

        $this->assertSame($firstTailor->id, $order->tailor_id);
        $this->assertDatabaseHas('tailor_order_offers', [
            'order_id' => $order->id,
            'tailor_id' => $secondTailor->id,
            'status' => TailorOrderOffer::STATUS_TAKEN,
        ]);
    }
}
