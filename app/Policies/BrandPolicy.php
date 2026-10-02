<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\Role;
use App\Models\User;

class BrandPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin() || $user->hasRole(Role::ADMINISTRADOR)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('products.view') || $user->hasPermission('brands.manage');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->hasPermission('products.view') || $user->hasPermission('brands.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('brands.manage') || $user->hasPermission('products.manage');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->hasPermission('brands.manage') || $user->hasPermission('products.manage');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->hasPermission('brands.manage') || $user->hasPermission('products.manage');
    }
}
