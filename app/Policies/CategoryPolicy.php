<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;

class CategoryPolicy
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
        return $user->hasPermission('products.view') || $user->hasPermission('categories.manage');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermission('products.view') || $user->hasPermission('categories.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('categories.manage') || $user->hasPermission('products.manage');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.manage') || $user->hasPermission('products.manage');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.manage') || $user->hasPermission('products.manage');
    }
}
