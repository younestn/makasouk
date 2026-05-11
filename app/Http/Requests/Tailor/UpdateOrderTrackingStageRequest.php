<?php

namespace App\Http\Requests\Tailor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderTrackingStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'stage' => ['required', 'string', 'max:64'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
