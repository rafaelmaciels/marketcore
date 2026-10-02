<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'description',
        'is_sales_floor',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_sales_floor' => 'boolean',
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Filial dona do depósito.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope para depósitos ativos.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
