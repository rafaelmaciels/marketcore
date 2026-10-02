<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $currentUser, string $ability): ?bool
    {
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $currentUser): bool
    {
        return $currentUser->hasPermission('users.view');
    }

    public function view(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->id === $targetUser->id) {
            return true;
        }

        // Não superadmin não pode visualizar superadmin
        if ($targetUser->isSuperAdmin()) {
            return false;
        }

        // Deve pertencer à mesma empresa
        return $currentUser->company_id === $targetUser->company_id && $currentUser->hasPermission('users.view');
    }

    public function create(User $currentUser): bool
    {
        return $currentUser->hasPermission('users.manage');
    }

    public function update(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->id === $targetUser->id) {
            return true;
        }

        if ($targetUser->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return false;
        }

        return $currentUser->company_id === $targetUser->company_id && $currentUser->hasPermission('users.manage');
    }

    public function delete(User $currentUser, User $targetUser): bool
    {
        // Não pode deletar a si próprio
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        if ($targetUser->isSuperAdmin()) {
            return false;
        }

        return $currentUser->company_id === $targetUser->company_id && $currentUser->hasPermission('users.manage');
    }
}
