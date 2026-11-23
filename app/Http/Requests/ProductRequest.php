<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->product_id;

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'category_id')],
            'product_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'product_name')
                    ->where(fn ($query) => $query->where('category_id', $this->input('category_id')))
                    ->ignore($productId, 'product_id'),
            ],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:20'],
            'unit_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
        ];
    }
}
