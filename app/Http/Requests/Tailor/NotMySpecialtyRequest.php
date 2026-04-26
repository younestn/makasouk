<?php

namespace App\Http\Requests\Tailor;

use Illuminate\Foundation\Http\FormRequest;

class NotMySpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
