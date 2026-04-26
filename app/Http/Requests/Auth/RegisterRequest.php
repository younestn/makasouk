<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\Tailor\TailorOnboardingOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isTailor = $this->input('role') === User::ROLE_TAILOR;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', Rule::in([User::ROLE_CUSTOMER, User::ROLE_TAILOR])],
            'device_name' => ['nullable', 'string', 'max:255'],
            'phone' => [
                Rule::requiredIf($isTailor),
                'nullable',
                'string',
                'min:8',
                'max:32',
                'regex:/^\+?[0-9]{8,16}$/',
                Rule::unique('users', 'phone'),
            ],
            'specialization' => [
                Rule::requiredIf($isTailor),
                'nullable',
                Rule::in(TailorOnboardingOptions::SPECIALIZATIONS),
            ],
            'work_wilaya' => [
                Rule::requiredIf($isTailor),
                'nullable',
                Rule::in(TailorOnboardingOptions::WILAYAS),
            ],
            'years_of_experience' => [
                Rule::requiredIf($isTailor),
                'nullable',
                'integer',
                'min:0',
                'max:80',
            ],
            'gender' => [
                Rule::requiredIf($isTailor),
                'nullable',
                Rule::in(array_keys(TailorOnboardingOptions::genderOptions())),
            ],
            'workers_count' => [
                Rule::requiredIf($isTailor),
                'nullable',
                'integer',
                'min:1',
                'max:1000',
            ],
            'commercial_register_file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = (string) $this->input('phone', '');
        if ($phone !== '') {
            $normalized = preg_replace('/[^0-9+]/', '', $phone) ?? $phone;
            $this->merge(['phone' => $normalized]);
        }
    }
}
