<?php

namespace App\Http\Requests\Tailor;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                Order::STATUS_PROCESSING,
                Order::STATUS_READY_FOR_DELIVERY,
                Order::STATUS_COMPLETED,
            ])],
        ];
    }
}
