<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'customer_id' => $this->customer_id,
            'tailor_id' => $this->tailor_id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'customer' => $this->whenLoaded('customer', fn (): array => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ]),
            'tailor' => $this->whenLoaded('tailor', fn (): array => [
                'id' => $this->tailor->id,
                'name' => $this->tailor->name,
            ]),
            'order' => $this->whenLoaded('order', fn (): array => [
                'id' => $this->order->id,
                'status' => $this->order->status,
                'product' => $this->order->relationLoaded('product') && $this->order->product
                    ? [
                        'id' => $this->order->product->id,
                        'name' => $this->order->product->name,
                        'main_image_url' => $this->order->product->main_image_url,
                    ]
                    : null,
            ]),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
