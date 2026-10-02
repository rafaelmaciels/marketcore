<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Category::class);
    }

    public function rules(): array
    {
        return [
            'level'       => ['required', 'in:department,category,subcategory'],
            'parent_id'   => ['nullable', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'status'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'O nome da categoria/departamento é obrigatório.',
            'level.required' => 'O nível da hierarquia é obrigatório.',
        ];
    }
}
