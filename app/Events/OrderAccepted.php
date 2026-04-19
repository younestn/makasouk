<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderAccepted implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * @param array<int, int> $notifiedTailorIds
     */
    public function __construct(
        public Order $order,
        public int $acceptedByTailorId,
        public array $notifiedTailorIds = [],
    ) {
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return array_map(
            static fn (int $tailorId): PrivateChannel => new PrivateChannel("tailor.{$tailorId}"),
            $this->notifiedTailorIds,
        );
    }

    public function broadcastAs(): string
    {
        return 'order.accepted';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'accepted_by_tailor_id' => $this->acceptedByTailorId,
            'accepted_at' => optional($this->order->accepted_at)?->toISOString(),
        ];
    }
}
