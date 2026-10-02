<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
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
        return $user->hasPermission('audit.view');
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        if ($user->hasPermission('audit.view')) {
            // Se o log pertence a uma filial, verifica se o usuário tem acesso àquela filial
            if ($auditLog->branch_id) {
                return $user->hasAccessToBranch($auditLog->branch_id);
            }
            return true;
        }

        return false;
    }

    // Logs são append-only. Ninguém pode criar/editar/excluir logs de auditoria via tela
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    public function delete(User $user, AuditLog $auditLog): bool
    {
        return false;
    }
}
