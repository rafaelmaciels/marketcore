<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $activeBranchId = session('active_branch_id');

        if (!$user->hasRole($roles, $activeBranchId)) {
            abort(403, 'Acesso não autorizado para o seu perfil nesta filial.');
        }

        return $next($request);
    }
}
