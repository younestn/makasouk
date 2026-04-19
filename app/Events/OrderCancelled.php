<?php

namespace App\Events;

use App\Models\Order;
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
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'reason' => $this->order->cancellation_reason,
        ];
    }
}
