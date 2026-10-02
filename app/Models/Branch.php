<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'cnpj',
        'ie',
        'phone',
        'email',
        'zip_code',
        'address',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'is_headquarters',
        'status',
    ];

    protected $casts = [
        'is_headquarters' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Empresa à qual a filial pertence.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Depósitos pertencentes a esta filial.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    /**
     * Depósito principal / padrão.
     */
    public function defaultWarehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class)->where('is_default', true);
    }

    /**
     * Depósito correspondente à Área de Vendas (Gôndolas/Loja Física).
     */
    public function salesFloorWarehouse(): HasOne
    {
        return $this->hasOne(Warehouse::class)->where('is_sales_floor', true);
    }

    /**
     * Pontos de venda (PDVs) da filial.
     */
    public function pointsOfSale(): HasMany
    {
        return $this->hasMany(PointOfSale::class);
    }

    /**
     * Usuários com acesso à filial.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user')
            ->withPivot('role_id', 'is_default')
            ->withTimestamps();
    }

    /**
     * Scope para filiais ativas.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Endereço formatado para relatórios e cabeçalhos de cupom/documento.
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->number ? "nº {$this->number}" : null,
            $this->neighborhood,
            $this->city && $this->state ? "{$this->city}/{$this->state}" : null,
        ]);

        return implode(', ', $parts);
    }
}
