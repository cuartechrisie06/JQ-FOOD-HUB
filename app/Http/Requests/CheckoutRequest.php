<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fulfillment_method' => ['required', Rule::in(['Pickup', 'Delivery'])],
            'fulfillment_date' => ['required', 'date', 'after_or_equal:today'],
            'fulfillment_time' => ['required', 'date_format:H:i'],
            'delivery_address' => [
                Rule::requiredIf(
                    $this->input('fulfillment_method') === 'Delivery'
                    && ! $this->user()?->address
                ),
                'nullable',
                'string',
                'max:255',
            ],
            'payment_method' => ['required', Rule::in(['Cash', 'E-Wallet', 'Bank Transfer'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
