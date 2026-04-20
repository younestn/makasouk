<?php

namespace App\Events;

use App\Models\Order;
use App\Support\RealtimeOrderPayload;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['product', 'tailor']);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("customer.{$this->order->customer_id}");
    }

    public function broadcastAs(): string
    {
        return 'order.status_updated';
    }

    public function broadcastWith(): array
    {
        return [
            'event' => $this->broadcastAs(),
            'occurred_at' => now()->toISOString(),
            'order' => RealtimeOrderPayload::withParticipantSummary($this->order),
        ];
    }
}
