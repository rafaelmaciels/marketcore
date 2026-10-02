<?php

namespace App\Policies;

use App\Models\PointOfSale;
use App\Models\User;

class PointOfSalePolicy
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
        return $user->hasPermission('pos.view');
    }

    public function view(User $user, PointOfSale $pointOfSale): bool
    {
        return $user->hasAccessToBranch($pointOfSale->branch_id) && $user->hasPermission('pos.view', $pointOfSale->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('pos.manage');
    }

    public function update(User $user, PointOfSale $pointOfSale): bool
    {
        return $user->hasAccessToBranch($pointOfSale->branch_id) && $user->hasPermission('pos.manage', $pointOfSale->branch_id);
    }

    public function delete(User $user, PointOfSale $pointOfSale): bool
    {
        return $user->hasAccessToBranch($pointOfSale->branch_id) && $user->hasPermission('pos.manage', $pointOfSale->branch_id);
    }
}
