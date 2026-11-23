<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FoodPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'min_pax' => ['required', 'integer', 'min:1'],
            'max_pax' => ['required', 'integer', 'gte:min_pax'],
            'event_type' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
            'items' => ['nullable', 'array', 'max:50'],
            'items.*.item_description' => ['required_with:items', 'string', 'max:150'],
            'items.*.quantity' => ['required_with:items', 'string', 'max:50'],
            'items.*.package_item_id' => ['nullable', 'integer'],
        ];
    }
}
