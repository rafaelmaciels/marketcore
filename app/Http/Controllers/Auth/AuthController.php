<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Efetua autenticação do usuário com auditoria e controle de filiais.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            // Auditoria de tentativa fracassada
            AuditService::log(
                action: 'login_failed',
                description: 'Tentativa falha de login para o e-mail: ' . $request->input('email')
            );

            throw ValidationException::withMessages([
                'email' => 'As credenciais informadas não correspondem aos nossos registros.',
            ]);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // Verifica se o usuário está ativo
        if (!$user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            AuditService::log(
                action: 'login_blocked',
                description: "Tentativa de login de usuário inativo ou bloqueado: {$user->email}"
            );

            throw ValidationException::withMessages([
                'email' => 'Sua conta de usuário está inativa. Entre em contato com a administração.',
            ]);
        }

        // Atualiza dados de último acesso
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Determina a filial ativa padrão
        $defaultBranch = $user->getDefaultBranch();
        if ($defaultBranch) {
            session(['active_branch_id' => $defaultBranch->id]);
        }

        // Auditoria de login realizado com sucesso
        AuditService::log(
            action: 'login',
            description: "Login bem-sucedido do usuário {$user->name} ({$user->email})",
            auditable: $user,
            branchId: $defaultBranch?->id
        );

        return redirect()->intended(route('dashboard'))
            ->with('success', "Bem-vindo de volta, {$user->name}!");
    }

    /**
     * Finaliza a sessão do usuário.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditService::log(
                action: 'logout',
                description: "Logout efetuado pelo usuário {$user->name} ({$user->email})",
                auditable: $user
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Você encerrou sua sessão com sucesso.');
    }
}
