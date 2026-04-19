<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * @param array<int, int> $tailorIds
     * @param array<int, float> $distancesByTailorId
     */
    public function __construct(
        public Order $order,
        public array $tailorIds,
        public array $distancesByTailorId = [],
    ) {
        $this->order->loadMissing(['product.category']);
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return array_map(
            static fn (int $tailorId): PrivateChannel => new PrivateChannel("tailor.{$tailorId}"),
            $this->tailorIds,
        );
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'product' => [
                'id' => $this->order->product?->id,
                'name' => $this->order->product?->name,
                'category_id' => $this->order->product?->category_id,
                'category_name' => $this->order->product?->category?->name,
                'price' => $this->order->product?->price,
                'pricing_type' => $this->order->product?->pricing_type,
            ],
            'measurements' => $this->order->measurements,
            'delivery' => [
                'latitude' => $this->order->delivery_latitude,
                'longitude' => $this->order->delivery_longitude,
            ],
            'distances_by_tailor_id' => $this->distancesByTailorId,
            'created_at' => optional($this->order->created_at)?->toISOString(),
        ];
    }
}
