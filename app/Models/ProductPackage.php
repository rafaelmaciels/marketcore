<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'unit_type',
        'conversion_factor',
        'barcode',
        'is_default_sale',
        'status',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'is_default_sale'   => 'boolean',
        'status'            => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Converte uma quantidade desta embalagem para a unidade base do produto.
     * Exemplo: 2 caixas com fator 24 = 48 unidades base.
     */
    public function toBaseUnits(float $quantity): float
    {
        return (float) ($quantity * $this->conversion_factor);
    }

    /**
     * Converte uma quantidade em unidades base para esta embalagem.
     * Exemplo: 48 unidades base / fator 24 = 2 caixas.
     */
    public function fromBaseUnits(float $baseQuantity): float
    {
        if ($this->conversion_factor <= 0) {
            return 0.0;
        }

        return (float) ($baseQuantity / $this->conversion_factor);
    }
}
