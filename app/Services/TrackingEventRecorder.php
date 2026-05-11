<?php

namespace App\Services;

use App\Models\CustomOrder;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class TrackingEventRecorder
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function record(
        Model $trackable,
        string $code,
        ?string $responsibleRole = null,
        ?string $description = null,
        array $meta = [],
        ?\DateTimeInterface $occurredAt = null,
    ): void {
        if (! method_exists($trackable, 'trackingEvents')) {
            return;
        }

        $trackable->trackingEvents()->create([
            'code' => $code,
            'responsible_role' => $responsibleRole,
            'description' => $description,
            'meta' => $meta,
            'occurred_at' => $occurredAt ?? now(),
        ]);
    }

    public function seedInitialOrderTimeline(Order $order): void
    {
        if ($order->trackingEvents()->exists()) {
            return;
        }

        $this->record($order, 'placed', 'customer', __('messages.orders.timeline.placed'));
        $this->record($order, 'admin_review', 'system', __('messages.orders.timeline.admin_review'));
        $this->record($order, 'tailor_assignment_pending', 'system', __('messages.orders.timeline.tailor_assignment_pending'));
    }

    public function seedInitialCustomOrderTimeline(CustomOrder $customOrder): void
    {
        if ($customOrder->trackingEvents()->exists()) {
            return;
        }

        $this->record($customOrder, 'placed', 'customer', __('messages.custom_orders.timeline.placed'));
    }
}
