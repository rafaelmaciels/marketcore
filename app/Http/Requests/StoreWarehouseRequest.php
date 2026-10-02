<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Warehouse::class);
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:20'],
            'description'    => ['nullable', 'string', 'max:500'],
            'is_sales_floor' => ['nullable', 'boolean'],
            'is_default'     => ['nullable', 'boolean'],
            'status'         => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do depósito é obrigatório.',
        ];
    }
}
