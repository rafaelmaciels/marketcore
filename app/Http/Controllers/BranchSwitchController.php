<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchSwitchController extends Controller
{
    /**
     * Alterna a filial ativa do usuário na sessão.
     */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
        ]);

        $user = Auth::user();
        $targetBranchId = (int) $request->input('branch_id');
        $targetBranch = Branch::findOrFail($targetBranchId);

        // Validação rigorosa de permissão de acesso à filial
        if (!$user->hasAccessToBranch($targetBranchId)) {
            AuditService::log(
                action: 'unauthorized_branch_switch',
                description: "Tentativa não autorizada de alternar para a filial {$targetBranch->name} (ID: {$targetBranchId})",
                auditable: $targetBranch
            );

            abort(403, 'Você não tem permissão para acessar esta filial.');
        }

        $previousBranchId = session('active_branch_id');
        session(['active_branch_id' => $targetBranchId]);

        AuditService::log(
            action: 'switch_branch',
            description: "Filial ativa alterada para '{$targetBranch->name}' (ID: {$targetBranchId})",
            auditable: $targetBranch,
            oldValues: ['previous_branch_id' => $previousBranchId],
            newValues: ['new_branch_id' => $targetBranchId],
            branchId: $targetBranchId
        );

        return back()->with('success', "Filial ativa alterada para '{$targetBranch->name}'.");
    }
}
