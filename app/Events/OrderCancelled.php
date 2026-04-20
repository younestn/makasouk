<?php

namespace App\Events;

use App\Models\Order;
use App\Support\RealtimeOrderPayload;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCancelled implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        if ($this->order->tailor_id === null) {
            return [];
        }

        return [new PrivateChannel("tailor.{$this->order->tailor_id}")];
    }

    public function broadcastAs(): string
    {
        return 'order.cancelled_by_customer';
    }

    public function broadcastWith(): array
    {
        return [
            'event' => $this->broadcastAs(),
            'occurred_at' => now()->toISOString(),
            'order' => RealtimeOrderPayload::base($this->order),
            'meta' => [
                'cancelled_by' => 'customer',
            ],
        ];
    }
}
