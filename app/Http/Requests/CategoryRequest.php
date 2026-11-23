<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->category_id;

        return [
            'category_name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('categories', 'category_name')->ignore($categoryId, 'category_id'),
            ],
            'description' => ['nullable', 'string', 'max:200'],
        ];
    }
}
