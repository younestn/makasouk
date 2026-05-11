<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'nullable',
                'string',
                'min:8',
                'max:32',
                'regex:/^\+?[0-9]{8,16}$/',
                Rule::unique('users', 'phone')->ignore($this->user()?->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = (string) $this->input('phone', '');

        if ($phone !== '') {
            $this->merge([
                'phone' => preg_replace('/[^0-9+]/', '', $phone) ?? $phone,
            ]);
        }
    }
}
