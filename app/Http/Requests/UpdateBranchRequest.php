<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $branch = $this->route('branch');
        return $this->user()->can('update', $branch);
    }

    public function rules(): array
    {
        return [
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
}
