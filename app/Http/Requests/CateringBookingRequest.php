<?php

namespace App\Http\Requests;

use App\Models\FoodPackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CateringBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function getRedirectUrl(): string
    {
        return url()->previous().'#booking';
    }

    public function rules(): array
    {
        return [
            'slot_date' => ['required', 'date', 'after_or_equal:'.today()->addDays(3)->toDateString()],
            'event_type' => ['required', 'string', 'max:50'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'package_id' => [
                'required',
                'integer',
                Rule::exists('food_packages', 'package_id')->where('is_available', true),
            ],
            'drink_option' => [
                'nullable',
                'string',
                Rule::in(['Solo', 'Bottled Water', 'Softdrinks']),
            ],
            'event_time' => ['required', 'date_format:H:i'],
            'event_location' => ['required', 'string', 'max:255'],
            'special_requests' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $package = FoodPackage::where('package_id', $this->input('package_id'))
                ->where('is_available', true)
                ->first();

            if (! $package || ! is_numeric($this->input('guest_count'))) {
                return;
            }

            $guestCount = (int) $this->input('guest_count');

            if ($package->min_pax !== null && $guestCount < $package->min_pax) {
                $validator->errors()->add(
                    'guest_count',
                    "Guest count must be at least {$package->min_pax} for the selected package."
                );
            }

            if ($package->max_pax !== null && $guestCount > $package->max_pax) {
                $validator->errors()->add(
                    'guest_count',
                    "Guest count cannot exceed {$package->max_pax} for the selected package."
                );
            }
        });
    }
}
