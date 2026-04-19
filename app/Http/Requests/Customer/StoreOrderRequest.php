<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'measurements' => ['required', 'array', 'min:1'],
            'measurements.*' => ['nullable'],
            'customer_location' => ['required', 'array'],
            'customer_location.latitude' => ['required', 'numeric', 'between:-90,90'],
            'customer_location.longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
