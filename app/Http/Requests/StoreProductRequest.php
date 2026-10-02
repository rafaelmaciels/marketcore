<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    public function rules(): array
    {
        return [
            'sku'               => ['required', 'string', 'max:50', 'unique:products,sku'],
            'internal_code'     => ['nullable', 'string', 'max:50'],
            'name'              => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string'],
            'brand_id'          => ['nullable', 'exists:brands,id'],
            'department_id'     => ['nullable', 'exists:categories,id'],
            'category_id'       => ['nullable', 'exists:categories,id'],
            'subcategory_id'    => ['nullable', 'exists:categories,id'],
            'unit_of_measure'   => ['required', 'string', 'max:10'],
            'is_weighable'      => ['nullable', 'boolean'],
            'status'            => ['required', 'in:active,inactive,discontinued'],
            
            // Fiscal
            'ncm'               => ['nullable', 'string', 'max:10'],
            'cest'              => ['nullable', 'string', 'max:10'],
            'origin'            => ['nullable', 'string', 'max:2'],
            'cst_csosn'         => ['nullable', 'string', 'max:10'],
            'cfop_default'      => ['nullable', 'string', 'max:10'],
            
            // Físico
            'net_weight'        => ['nullable', 'numeric', 'min:0'],
            'gross_weight'      => ['nullable', 'numeric', 'min:0'],
            'volume'            => ['nullable', 'string', 'max:50'],
            
            // Estoque Teórico / Parâmetros
            'minimum_stock'     => ['nullable', 'numeric', 'min:0'],
            'maximum_stock'     => ['nullable', 'numeric', 'min:0'],
            'safety_stock'      => ['nullable', 'numeric', 'min:0'],

            // Códigos de Barras
            'barcodes'          => ['nullable', 'array'],
            'barcodes.*.barcode'=> ['required_with:barcodes', 'string', 'max:50'],
            'barcodes.*.type'   => ['required_with:barcodes', 'in:ean13,ean8,gtin,internal'],
            'barcodes.*.is_main'=> ['nullable', 'boolean'],

            // Embalagens
            'packages'                     => ['nullable', 'array'],
            'packages.*.name'              => ['required_with:packages', 'string', 'max:100'],
            'packages.*.unit_type'         => ['required_with:packages', 'string', 'max:10'],
            'packages.*.conversion_factor' => ['required_with:packages', 'numeric', 'min:0.0001'],
            'packages.*.barcode'           => ['nullable', 'string', 'max:50'],
            'packages.*.is_default_sale'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required'             => 'O SKU do produto é obrigatório.',
            'sku.unique'               => 'Este SKU já está cadastrado em outro produto.',
            'name.required'            => 'O nome do produto é obrigatório.',
            'unit_of_measure.required' => 'A unidade de medida de venda é obrigatória.',
        ];
    }
}
