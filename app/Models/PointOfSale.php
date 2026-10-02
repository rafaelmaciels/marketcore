<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PointOfSale extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'points_of_sale';

    protected $fillable = [
        'branch_id',
        'number',
        'name',
        'terminal_code',
        'ip_address',
        'status',
        'notes',
    ];

    /**
     * Filial dona deste PDV.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope para PDVs ativos.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
