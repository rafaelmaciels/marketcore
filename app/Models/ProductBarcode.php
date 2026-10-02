<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBarcode extends Model
{
    use HasFactory;

    public const TYPE_EAN13    = 'ean13';
    public const TYPE_EAN8     = 'ean8';
    public const TYPE_GTIN     = 'gtin';
    public const TYPE_INTERNAL = 'internal';

    protected $fillable = [
        'product_id',
        'barcode',
        'type',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
