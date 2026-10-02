<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'status',
        'is_superadmin',
        'all_branches',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_superadmin'     => 'boolean',
            'all_branches'      => 'boolean',
        ];
    }

    /**
     * Empresa principal à qual o usuário pertence.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Papel/Role global padrão do usuário.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Filiais associadas ao usuário.
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user')
            ->using(BranchUser::class)
            ->withPivot('id', 'role_id', 'is_default')
            ->withTimestamps();
    }

    /**
     * Logs de auditoria gerados por este usuário.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Verifica se o usuário é Superadmin do sistema.
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_superadmin || ($this->role && $this->role->name === Role::SUPERADMIN);
    }

    /**
     * Verifica se o usuário está com cadastro ativo.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Retorna a lista de filiais que o usuário pode acessar.
     */
    public function getAccessibleBranches(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Branch::with('company')->active()->orderBy('name')->get();
        }

        if ($this->all_branches && $this->company_id) {
            return Branch::where('company_id', $this->company_id)->active()->orderBy('name')->get();
        }

        return $this->branches()->active()->with('company')->orderBy('name')->get();
    }

    /**
     * Verifica se o usuário possui acesso à filial informada.
     */
    public function hasAccessToBranch(int|string|null $branchId): bool
    {
        if (!$branchId) {
            return false;
        }

        $branchId = (int) $branchId;

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->all_branches && $this->company_id) {
            return Branch::where('id', $branchId)
                ->where('company_id', $this->company_id)
                ->exists();
        }

        return $this->branches()->where('branches.id', $branchId)->exists();
    }

    /**
     * Retorna a role do usuário no contexto da filial especificada.
     */
    public function getRoleForBranch(?int $branchId = null): ?Role
    {
        if ($this->isSuperAdmin()) {
            return Role::where('name', Role::SUPERADMIN)->first() ?? $this->role;
        }

        if ($branchId) {
            $branchPivot = $this->branches()->where('branches.id', $branchId)->first()?->pivot;
            if ($branchPivot && $branchPivot->role_id) {
                return Role::find($branchPivot->role_id);
            }
        }

        return $this->role;
    }

    /**
     * Verifica se o usuário possui determinada(s) role(s) na filial ativa ou globalmente.
     */
    public function hasRole(string|array $roleNames, ?int $branchId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $roleNames = is_array($roleNames) ? $roleNames : [$roleNames];
        $currentRole = $this->getRoleForBranch($branchId);

        if (!$currentRole) {
            return false;
        }

        return in_array($currentRole->name, $roleNames, true);
    }

    /**
     * Verifica se o usuário tem determinada permissão (considerando superadmin e filial ativa).
     */
    public function hasPermission(string $permissionName, ?int $branchId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $currentRole = $this->getRoleForBranch($branchId);
        if (!$currentRole) {
            return false;
        }

        return $currentRole->hasPermissionTo($permissionName);
    }

    /**
     * Retorna a filial padrão/inicial do usuário.
     */
    public function getDefaultBranch(): ?Branch
    {
        // 1. Filial marcada como padrão na pivô
        $defaultPivotBranch = $this->branches()->wherePivot('is_default', true)->first();
        if ($defaultPivotBranch) {
            return $defaultPivotBranch;
        }

        // 2. Primeira filial vinculada
        $firstBranch = $this->branches()->first();
        if ($firstBranch) {
            return $firstBranch;
        }

        // 3. Se tiver acesso a todas, pega a matriz da empresa ou primeira ativa
        if ($this->isSuperAdmin() || $this->all_branches) {
            $query = Branch::query()->active();
            if ($this->company_id) {
                $query->where('company_id', $this->company_id);
            }
            return $query->orderByDesc('is_headquarters')->first();
        }

        return null;
    }
}
