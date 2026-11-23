<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'full_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id'),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['required', Rule::in(['Individual', 'Business'])],
            'business_name' => [
                Rule::requiredIf($this->input('customer_type') === 'Business'),
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }
}
