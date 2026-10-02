<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const SUPERADMIN    = 'superadmin';
    public const ADMINISTRADOR = 'administrador';
    public const GERENTE       = 'gerente';
    public const COMPRADOR     = 'comprador';
    public const ESTOQUE       = 'estoque';
    public const FISCAL        = 'fiscal';
    public const FINANCEIRO    = 'financeiro';
    public const OPERADOR      = 'operador';
    public const AUDITOR       = 'auditor';

    protected $fillable = [
        'name',
        'label',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Permissões associadas a esta role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Usuários que possuem esta role como perfil padrão/global.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Vínculos de usuários por filial com esta role.
     */
    public function branchUsers(): HasMany
    {
        return $this->hasMany(BranchUser::class);
    }

    /**
     * Verifica se a role possui determinada permissão.
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        if ($this->name === self::SUPERADMIN) {
            return true;
        }

        return $this->permissions->contains('name', $permissionName);
    }
}
