<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    public const STATUS_ACTIVE       = 'active';
    public const STATUS_INACTIVE     = 'inactive';
    public const STATUS_DISCONTINUED = 'discontinued';

    protected $fillable = [
        'company_id',
        'sku',
        'internal_code',
        'name',
        'short_description',
        'description',
        'brand_id',
        'department_id',
        'category_id',
        'subcategory_id',
        'unit_of_measure',
        'is_weighable',
        'status',
        'ncm',
        'cest',
        'origin',
        'cst_csosn',
        'cfop_default',
        'net_weight',
        'gross_weight',
        'volume',
        'minimum_stock',
        'maximum_stock',
        'safety_stock',
    ];

    protected $casts = [
        'is_weighable'   => 'boolean',
        'net_weight'     => 'decimal:3',
        'gross_weight'   => 'decimal:3',
        'minimum_stock'  => 'decimal:3',
        'maximum_stock'  => 'decimal:3',
        'safety_stock'   => 'decimal:3',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'department_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function mainBarcode(): HasOne
    {
        return $this->hasOne(ProductBarcode::class)->where('is_main', true);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ProductPackage::class);
    }

    public function defaultPackage(): HasOne
    {
        return $this->hasOne(ProductPackage::class)->where('is_default_sale', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeWeighable($query)
    {
        return $query->where('is_weighable', true);
    }

    /**
     * Scope para busca avançada por nome, SKU, código interno ou código de barras.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('internal_code', 'like', "%{$term}%")
              ->orWhereHas('barcodes', function ($bQuery) use ($term) {
                  $bQuery->where('barcode', 'like', "%{$term}%");
              });
        });
    }

    /**
     * Converte uma quantidade entre embalagens do mesmo produto.
     */
    public function convertQuantity(float $quantity, ProductPackage $fromPackage, ?ProductPackage $toPackage = null): float
    {
        $baseUnits = $fromPackage->toBaseUnits($quantity);

        if (!$toPackage) {
            return $baseUnits;
        }

        return $toPackage->fromBaseUnits($baseUnits);
    }
}
