<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'tailor_id' => $this->tailor_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'measurements' => $this->measurements,
            'delivery_latitude' => $this->delivery_latitude,
            'delivery_longitude' => $this->delivery_longitude,
            'cancellation_reason' => $this->cancellation_reason,
            'accepted_at' => optional($this->accepted_at)?->toISOString(),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
            'review' => new ReviewResource($this->whenLoaded('review')),
        ];
    }
}
