<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;

class ProductPolicy
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
        return $user->hasPermission('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermission('products.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('products.create') || $user->hasPermission('products.manage');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermission('products.edit') || $user->hasPermission('products.manage');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermission('products.delete') || $user->hasPermission('products.manage');
    }
}
