<?php

namespace App\Http\Resources;

use App\Support\OrderLifecycle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = (string) $this->status;

        return [
            'id' => $this->id,
            'status' => $status,
            'customer_id' => $this->customer_id,
            'tailor_id' => $this->tailor_id,
            'product_id' => $this->product_id,
            'measurements' => $this->measurements,
            'delivery' => [
                'latitude' => $this->delivery_latitude,
                'longitude' => $this->delivery_longitude,
            ],
            'cancellation_reason' => $this->cancellation_reason,
            'timestamps' => [
                'accepted_at' => optional($this->accepted_at)?->toISOString(),
                'created_at' => optional($this->created_at)?->toISOString(),
                'updated_at' => optional($this->updated_at)?->toISOString(),
            ],
            'product' => new ProductResource($this->whenLoaded('product')),
            'customer' => $this->whenLoaded('customer', fn (): array => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ]),
            'tailor' => $this->whenLoaded('tailor', fn (): array => [
                'id' => $this->tailor->id,
                'name' => $this->tailor->name,
            ]),
            'review' => new ReviewResource($this->whenLoaded('review')),
            'lifecycle' => [
                'allowed_next_statuses_for_tailor' => OrderLifecycle::allowedTailorNextStatuses($status),
                'customer_can_cancel' => in_array($status, OrderLifecycle::customerCancellableStatuses(), true),
                'tailor_can_cancel' => in_array($status, OrderLifecycle::tailorCancellableStatuses(), true),
                'is_terminal' => in_array($status, OrderLifecycle::terminalStatuses(), true),
            ],
        ];
    }
}