<?php

namespace App\Support;

use App\Models\Order;
use App\Services\OrderFinancialsService;

class RealtimeOrderPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function base(Order $order): array
    {
        return [
            'id' => $order->id,
            'status' => $order->status,
            'customer_id' => $order->customer_id,
            'tailor_id' => $order->tailor_id,
            'product_id' => $order->product_id,
            'cancellation_reason' => $order->cancellation_reason,
            'accepted_at' => optional($order->accepted_at)?->toISOString(),
            'created_at' => optional($order->created_at)?->toISOString(),
            'updated_at' => optional($order->updated_at)?->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function withProductAndDelivery(Order $order): array
    {
        $order->loadMissing(['product.category', 'product.fabric']);

        return array_merge(self::base($order), [
            'product' => [
                'id' => $order->product?->id,
                'name' => $order->product?->name,
                'category_id' => $order->product?->category_id,
                'category_name' => $order->product?->category?->display_name,
                'category_specialization' => $order->product?->category?->tailor_specialization,
                'price' => $order->product?->price,
                'pricing_type' => $order->product?->pricing_type,
                'fabric_type' => $order->product?->display_fabric_type,
                'fabric_country' => $order->product?->display_fabric_country,
                'fabric_description' => $order->product?->display_fabric_description,
                'fabric_image_url' => $order->product?->fabric_image_url,
            ],
            'measurements' => $order->measurements,
            'delivery' => [
                'latitude' => null,
                'longitude' => null,
                'work_wilaya' => $order->delivery_work_wilaya,
                'label' => null,
                'preview' => $order->delivery_work_wilaya,
                'is_limited' => true,
            ],
            'financials' => app(OrderFinancialsService::class)->payload($order),
            'fulfillment' => [
                'pattern_available' => (bool) ($order->product?->has_pattern_files ?? false),
                'pattern_locked' => (bool) ($order->product?->has_pattern_files ?? false),
                'pattern_file_url' => null,
                'pattern_file_urls' => [],
            ],
            'matching' => [
                'matched_specialization' => $order->matched_specialization,
                'recommended_tailor_id' => data_get($order->matching_snapshot, 'recommended_tailor_id'),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function withParticipantSummary(Order $order): array
    {
        $order->loadMissing(['tailor', 'customer', 'product.fabric']);

        return array_merge(self::base($order), [
            'customer' => [
                'id' => $order->customer?->id,
                'name' => $order->customer?->name,
            ],
            'tailor' => [
                'id' => $order->tailor?->id,
                'name' => $order->tailor?->name,
            ],
            'product' => [
                'id' => $order->product?->id,
                'name' => $order->product?->name,
                'fabric_type' => $order->product?->display_fabric_type,
                'fabric_country' => $order->product?->display_fabric_country,
                'fabric_image_url' => $order->product?->fabric_image_url,
            ],
        ]);
    }
}
