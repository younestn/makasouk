<?php

namespace App\Events;

use App\Models\Order;
use App\Support\RealtimeOrderPayload;
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
        $tailorChannels = array_map(
            static fn (int $tailorId): PrivateChannel => new PrivateChannel("tailor.{$tailorId}"),
            $this->notifiedTailorIds,
        );

        $tailorChannels[] = new PrivateChannel("customer.{$this->order->customer_id}");

        return $tailorChannels;
    }

    public function broadcastAs(): string
    {
        return 'order.accepted';
    }

    public function broadcastWith(): array
    {
        return [
            'event' => $this->broadcastAs(),
            'occurred_at' => now()->toISOString(),
            'order' => RealtimeOrderPayload::withParticipantSummary($this->order),
            'meta' => [
                'accepted_by_tailor_id' => $this->acceptedByTailorId,
                'notified_tailor_ids' => $this->notifiedTailorIds,
            ],
        ];
    }
}
