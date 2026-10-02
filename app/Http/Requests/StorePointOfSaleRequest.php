<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePointOfSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\PointOfSale::class);
    }

    public function rules(): array
    {
        return [
            'number'        => ['required', 'integer', 'min:1', 'max:999'],
            'name'          => ['required', 'string', 'max:255'],
            'terminal_code' => ['nullable', 'string', 'max:50'],
            'ip_address'    => ['nullable', 'ip'],
            'status'        => ['required', 'in:active,inactive,maintenance'],
            'notes'         => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'O número do PDV/Caixa é obrigatório.',
            'name.required'   => 'A identificação do PDV é obrigatória.',
        ];
    }
}
