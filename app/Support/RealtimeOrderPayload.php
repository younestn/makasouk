<?php

namespace App\Support;

use App\Models\Order;

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
        $order->loadMissing(['product.category']);

        return array_merge(self::base($order), [
            'product' => [
                'id' => $order->product?->id,
                'name' => $order->product?->name,
                'category_id' => $order->product?->category_id,
                'category_name' => $order->product?->category?->name,
                'price' => $order->product?->price,
                'pricing_type' => $order->product?->pricing_type,
            ],
            'measurements' => $order->measurements,
            'delivery' => [
                'latitude' => $order->delivery_latitude,
                'longitude' => $order->delivery_longitude,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function withParticipantSummary(Order $order): array
    {
        $order->loadMissing(['tailor', 'customer', 'product']);

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
            ],
        ]);
    }
}
