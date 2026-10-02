<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Consulta e listagem de logs de auditoria.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);
        $currentUser = Auth::user();

        $query = AuditLog::with(['user', 'branch'])->latest('created_at');

        // Se não for superadmin, restringe aos logs das filiais permitidas ou do usuário
        if (!$currentUser->isSuperAdmin()) {
            if ($currentUser->all_branches && $currentUser->company_id) {
                $branchIds = Branch::where('company_id', $currentUser->company_id)->pluck('id')->toArray();
                $query->whereIn('branch_id', $branchIds);
            } else {
                $branchIds = $currentUser->branches()->pluck('branches.id')->toArray();
                $query->whereIn('branch_id', $branchIds);
            }
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('auditable_type', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        $branches = $currentUser->getAccessibleBranches();
        $users = $currentUser->isSuperAdmin()
            ? User::orderBy('name')->get()
            : User::where('company_id', $currentUser->company_id)->orderBy('name')->get();

        return view('audit.index', compact('logs', 'branches', 'users'));
    }

    /**
     * Exibe os detalhes de um log de auditoria (valores antigos x novos).
     */
    public function show(AuditLog $auditLog): View
    {
        $this->authorize('view', $auditLog);
        $auditLog->load(['user', 'branch']);

        return view('audit.show', compact('auditLog'));
    }
}
