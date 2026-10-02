<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\PointOfSale;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Exibe o painel principal do ERP MarketCore.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $activeBranchId = session('active_branch_id');
        $activeBranch = $activeBranchId ? Branch::with(['company', 'warehouses', 'pointsOfSale'])->find($activeBranchId) : null;

        // Estatísticas da filial ativa
        $warehousesCount = 0;
        $activePosCount = 0;
        $usersCount = 0;
        $recentAudits = collect();

        if ($activeBranch) {
            $warehousesCount = Warehouse::where('branch_id', $activeBranch->id)->count();
            $activePosCount  = PointOfSale::where('branch_id', $activeBranch->id)->active()->count();
            $usersCount      = $activeBranch->users()->count();

            // Logs recentes da filial ativa
            $recentAudits = AuditLog::with('user')
                ->where('branch_id', $activeBranch->id)
                ->latest('created_at')
                ->limit(8)
                ->get();
        } elseif ($user->isSuperAdmin()) {
            $warehousesCount = Warehouse::count();
            $activePosCount  = PointOfSale::active()->count();
            $usersCount      = User::count();
            $recentAudits    = AuditLog::with(['user', 'branch'])->latest('created_at')->limit(8)->get();
        }

        $accessibleBranches = $user->getAccessibleBranches();
        $currentRole = $user->getRoleForBranch($activeBranchId);

        return view('dashboard.index', compact(
            'user',
            'activeBranch',
            'currentRole',
            'accessibleBranches',
            'warehousesCount',
            'activePosCount',
            'usersCount',
            'recentAudits'
        ));
    }
}
