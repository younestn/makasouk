<?php

namespace App\Events;

use App\Models\Order;
use App\Support\RealtimeOrderPayload;
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
        $this->order->loadMissing(['product.category', 'product.fabric']);
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
            'event' => $this->broadcastAs(),
            'occurred_at' => now()->toISOString(),
            'order' => RealtimeOrderPayload::withProductAndDelivery($this->order),
            'meta' => [
                'notified_tailor_ids' => $this->tailorIds,
                'distances_by_tailor_id' => $this->distancesByTailorId,
                'matched_specialization' => $this->order->matched_specialization,
                'recommended_tailor_id' => data_get($this->order->matching_snapshot, 'recommended_tailor_id'),
            ],
        ];
    }
}
