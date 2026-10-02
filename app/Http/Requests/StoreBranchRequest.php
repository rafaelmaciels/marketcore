<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Branch::class);
    }

    public function rules(): array
    {
        return [
            'company_id'      => ['required', 'exists:companies,id'],
            'name'            => ['required', 'string', 'max:255'],
            'code'            => ['nullable', 'string', 'max:20'],
            'cnpj'            => ['required', 'string', 'max:20'],
            'ie'              => ['nullable', 'string', 'max:30'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:255'],
            'zip_code'        => ['nullable', 'string', 'max:10'],
            'address'         => ['nullable', 'string', 'max:255'],
            'number'          => ['nullable', 'string', 'max:20'],
            'complement'      => ['nullable', 'string', 'max:100'],
            'neighborhood'    => ['nullable', 'string', 'max:100'],
            'city'            => ['nullable', 'string', 'max:100'],
            'state'           => ['nullable', 'string', 'size:2'],
            'is_headquarters' => ['nullable', 'boolean'],
            'status'          => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'O nome da filial é obrigatório.',
            'cnpj.required'       => 'O CNPJ da filial é obrigatório.',
            'company_id.required' => 'Selecione a empresa à qual a filial pertence.',
        ];
    }
}
