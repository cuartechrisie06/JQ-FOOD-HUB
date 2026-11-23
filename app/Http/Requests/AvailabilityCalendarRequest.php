<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvailabilityCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slot_date' => ['required', 'date'],
            'max_bookings' => ['required', 'integer', 'min:1', 'max:1000'],
            'is_blocked' => ['sometimes', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:150'],
        ];
    }
}
