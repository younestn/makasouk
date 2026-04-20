<?php

namespace App\Http\Requests\Tailor;

use Illuminate\Foundation\Http\FormRequest;

class AcceptOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'notified_tailor_ids' => ['nullable', 'array'],
            'notified_tailor_ids.*' => ['integer', 'distinct'],
        ];
    }
}
