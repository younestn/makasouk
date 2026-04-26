<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => optional($this->email_verified_at)?->toISOString(),
            'email_is_verified' => $this->email_verified_at !== null,
            'phone' => $this->phone,
            'phone_verified_at' => optional($this->phone_verified_at)?->toISOString(),
            'phone_is_verified' => $this->phone_verified_at !== null,
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
                    'category_specialization' => $this->tailorProfile->relationLoaded('category')
                        ? $this->tailorProfile->category?->tailor_specialization
                        : null,
                    'average_rating' => (float) $this->tailorProfile->average_rating,
                    'total_reviews' => (int) $this->tailorProfile->total_reviews,
                    'latitude' => $this->tailorProfile->latitude,
                    'longitude' => $this->tailorProfile->longitude,
                    'specialization' => $this->tailorProfile->specialization,
                    'work_wilaya' => $this->tailorProfile->work_wilaya,
                    'years_of_experience' => $this->tailorProfile->years_of_experience,
                    'gender' => $this->tailorProfile->gender,
                    'workers_count' => $this->tailorProfile->workers_count,
                    'commercial_register_url' => filled($this->tailorProfile->commercial_register_path)
                        ? Storage::disk('public')->url($this->tailorProfile->commercial_register_path)
                        : null,
                ],
            ),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
