<?php

namespace App\Http\Requests;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Brand::class);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'status'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da marca é obrigatório.',
        ];
    }
}
