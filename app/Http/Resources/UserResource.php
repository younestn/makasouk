<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_suspended' => (bool) $this->is_suspended,
            'approved_at' => optional($this->approved_at)?->toISOString(),
            'tailor_profile' => $this->when(
                $this->relationLoaded('tailorProfile') && $this->tailorProfile !== null,
                fn (): array => [
                    'id' => $this->tailorProfile->id,
                    'status' => $this->tailorProfile->status,
                    'category_id' => $this->tailorProfile->category_id,
                    'category_name' => $this->tailorProfile->relationLoaded('category')
                        ? $this->tailorProfile->category?->name
                        : null,
                    'average_rating' => (float) $this->tailorProfile->average_rating,
                    'total_reviews' => (int) $this->tailorProfile->total_reviews,
                    'latitude' => $this->tailorProfile->latitude,
                    'longitude' => $this->tailorProfile->longitude,
                ],
            ),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
