<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'fabric_id' => ['nullable', 'integer', 'exists:fabrics,id'],
            'fabric_type' => ['nullable', 'string', 'max:120'],
            'fabric_country' => ['nullable', 'string', 'max:120'],
            'fabric_description' => ['nullable', 'string'],
            'fabric_image_path' => ['nullable', 'string', 'max:255'],
            'pricing_type' => ['required', 'in:fixed,estimated'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'measurement_ids' => ['nullable', 'array'],
            'measurement_ids.*' => [
                'integer',
                Rule::exists('measurements', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ];
    }
}
