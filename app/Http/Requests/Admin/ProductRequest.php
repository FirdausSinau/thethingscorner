<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:150'],
            'category_id'          => ['required', 'exists:categories,id'],
            'description'          => ['nullable', 'string', 'max:2000'],
            'base_price'           => ['required', 'integer', 'min:0'],
            'weight_grams'         => ['nullable', 'integer', 'min:0'],
            'is_active'            => ['nullable', 'boolean'],
            'is_preorder'          => ['nullable', 'boolean'],
            'preorder_close_date'  => ['nullable', 'date'],
            'estimated_ready_date' => ['nullable', 'date'],
        ];
    }
}