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
            'tailor_profile' => $this->whenLoaded('tailorProfile'),
            'created_at' => optional($this->created_at)?->toISOString(),
        ];
    }
}
