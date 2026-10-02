<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'trade_name',
        'cnpj',
        'ie',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Filiais pertencentes à empresa.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Usuários vinculados a esta empresa.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Depósitos de todas as filiais desta empresa.
     */
    public function warehouses(): HasManyThrough
    {
        return $this->hasManyThrough(Warehouse::class, Branch::class);
    }

    /**
     * Pontos de venda (PDVs) de todas as filiais desta empresa.
     */
    public function pointsOfSale(): HasManyThrough
    {
        return $this->hasManyThrough(PointOfSale::class, Branch::class);
    }

    /**
     * Scope para empresas ativas.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
