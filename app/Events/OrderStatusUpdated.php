<?php

namespace App\Events;

use App\Models\Order;
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
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'tailor_id' => $this->order->tailor_id,
            'tailor_name' => $this->order->tailor?->name,
            'product_name' => $this->order->product?->name,
            'updated_at' => optional($this->order->updated_at)?->toISOString(),
        ];
    }
}
