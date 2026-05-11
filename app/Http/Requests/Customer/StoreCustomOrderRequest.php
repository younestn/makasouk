<?php

namespace App\Http\Requests\Customer;

use App\Support\Geo\AlgeriaBounds;
use App\Support\Tailor\TailorOnboardingOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'tailor_specialty' => ['required', Rule::in(TailorOnboardingOptions::SPECIALIZATIONS)],
            'fabric_type' => ['nullable', 'string', 'max:120'],
            'measurements' => ['nullable', 'array'],
            'measurements.*' => ['nullable', 'numeric', 'min:0.1', 'max:350'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'reference_images' => ['required', 'array', 'min:1', 'max:6'],
            'reference_images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'customer_location' => ['required', 'array'],
            'customer_location.latitude' => ['required', 'numeric', 'between:-90,90'],
            'customer_location.longitude' => ['required', 'numeric', 'between:-180,180'],
            'customer_location.work_wilaya' => ['required', Rule::in(TailorOnboardingOptions::WILAYAS)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $latitude = $this->input('customer_location.latitude');
            $longitude = $this->input('customer_location.longitude');

            if (is_numeric($latitude) && is_numeric($longitude) && ! AlgeriaBounds::contains((float) $latitude, (float) $longitude)) {
                $validator->errors()->add('customer_location.latitude', __('messages.validation.location_must_be_in_algeria'));
            }
        });
    }
}
