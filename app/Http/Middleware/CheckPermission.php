<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $activeBranchId = session('active_branch_id');

        if (!$user->hasPermission($permission, $activeBranchId)) {
            abort(403, "Você não possui a permissão '{$permission}' nesta filial.");
        }

        return $next($request);
    }
}
