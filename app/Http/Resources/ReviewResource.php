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
            'rating' => $this->rating,
            'comment' => $this->comment,
            'customer_id' => $this->customer_id,
            'tailor_id' => $this->tailor_id,
            'created_at' => optional($this->created_at)?->toISOString(),
        ];
    }
}
