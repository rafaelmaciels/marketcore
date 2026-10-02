<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveBranch
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $accessibleBranches = $user->getAccessibleBranches();
        $activeBranchId = session('active_branch_id');

        // Se não houver filial ativa ou o usuário não tiver acesso à filial atual
        if (!$activeBranchId || !$user->hasAccessToBranch($activeBranchId)) {
            $defaultBranch = $user->getDefaultBranch();

            if ($defaultBranch) {
                session(['active_branch_id' => $defaultBranch->id]);
                $activeBranchId = $defaultBranch->id;
            } else {
                // Usuário sem filiais vinculadas (a menos que seja superadmin)
                if (!$user->isSuperAdmin()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->withErrors([
                        'email' => 'Seu usuário não possui acesso a nenhuma filial cadastrada. Contate o administrador.',
                    ]);
                }
            }
        }

        // Carrega a filial ativa para compartilhamento global
        $activeBranch = $activeBranchId ? Branch::with('company')->find($activeBranchId) : null;
        $activeRole = $user->getRoleForBranch($activeBranchId);

        // Compartilha com todas as views Blade
        View::share('activeBranch', $activeBranch);
        View::share('activeRole', $activeRole);
        View::share('accessibleBranches', $accessibleBranches);

        return $next($request);
    }
}
