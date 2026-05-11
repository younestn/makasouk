<?php

namespace App\Http\Resources;

use App\Support\OrderTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $trackingEvents = $this->relationLoaded('trackingEvents')
            ? $this->trackingEvents
            : collect();

        $timeline = OrderTracking::buildTimeline(
            $this->resource,
            $trackingEvents,
            (string) $this->status,
        );

        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => (string) $this->status,
            'tailor_specialty' => $this->tailor_specialty,
            'fabric_type' => $this->fabric_type,
            'measurements' => $this->measurements ?? [],
            'notes' => $this->notes,
            'quote' => [
                'amount' => $this->quote_amount !== null ? (float) $this->quote_amount : null,
                'note' => $this->quote_note,
                'quoted_at' => optional($this->quoted_at)?->toISOString(),
                'rejection_note' => $this->quote_rejection_note,
            ],
            'delivery' => [
                'latitude' => $this->delivery_latitude,
                'longitude' => $this->delivery_longitude,
                'work_wilaya' => $this->delivery_work_wilaya,
            ],
            'customer' => $this->whenLoaded('customer', fn (): array => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ]),
            'tailor' => $this->whenLoaded('tailor', fn (): ?array => $this->tailor ? [
                'id' => $this->tailor->id,
                'name' => $this->tailor->name,
                'email' => $this->tailor->email,
            ] : null),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image): array => [
                'id' => $image->id,
                'url' => $image->image_url,
                'sort_order' => (int) $image->sort_order,
            ])->values()->all()),
            'timeline' => $timeline,
            'meta' => [
                'can_accept_quote' => (string) $this->status === \App\Models\CustomOrder::STATUS_QUOTED,
                'can_reject_quote' => (string) $this->status === \App\Models\CustomOrder::STATUS_QUOTED,
                'has_assigned_tailor' => $this->tailor_id !== null,
            ],
            'timestamps' => [
                'created_at' => optional($this->created_at)?->toISOString(),
                'updated_at' => optional($this->updated_at)?->toISOString(),
                'accepted_at' => optional($this->accepted_at)?->toISOString(),
                'assigned_at' => optional($this->assigned_at)?->toISOString(),
            ],
        ];
    }
}
