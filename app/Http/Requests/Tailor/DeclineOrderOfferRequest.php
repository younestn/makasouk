<?php

namespace App\Http\Requests\Tailor;

use App\Models\TailorOrderOffer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeclineOrderOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', Rule::in([
                TailorOrderOffer::REASON_UNAVAILABLE,
                TailorOrderOffer::REASON_WORKLOAD_FULL,
                TailorOrderOffer::REASON_MEASUREMENTS_UNCLEAR,
                TailorOrderOffer::REASON_PRICING_NOT_SUITABLE,
                TailorOrderOffer::REASON_SHIPPING_TOO_FAR,
                TailorOrderOffer::REASON_OTHER,
            ])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
