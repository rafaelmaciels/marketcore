<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('warehouses.view');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->hasAccessToBranch($warehouse->branch_id) && $user->hasPermission('warehouses.view', $warehouse->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('warehouses.manage');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasAccessToBranch($warehouse->branch_id) && $user->hasPermission('warehouses.manage', $warehouse->branch_id);
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        // Depósitos padrão ou área de vendas principal não devem ser excluídos
        if ($warehouse->is_default || $warehouse->is_sales_floor) {
            return false;
        }

        return $user->hasAccessToBranch($warehouse->branch_id) && $user->hasPermission('warehouses.manage', $warehouse->branch_id);
    }
}
