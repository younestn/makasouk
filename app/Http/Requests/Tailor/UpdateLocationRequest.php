<?php

namespace App\Http\Requests\Tailor;

use App\Models\User;
use App\Support\Geo\AlgeriaBounds;
use App\Support\Tailor\TailorOnboardingOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_TAILOR;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'work_wilaya' => ['nullable', Rule::in(TailorOnboardingOptions::WILAYAS)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $latitude = $this->input('latitude');
            $longitude = $this->input('longitude');

            if ($latitude === null || $longitude === null) {
                return;
            }

            if (! is_numeric($latitude) || ! is_numeric($longitude)) {
                return;
            }

            if (! AlgeriaBounds::contains((float) $latitude, (float) $longitude)) {
                $validator->errors()->add('latitude', __('messages.validation.location_must_be_in_algeria'));
            }
        });
    }
}
