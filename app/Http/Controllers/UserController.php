<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Listagem de usuários com filtros.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);
        $currentUser = Auth::user();

        $query = User::with(['role', 'company', 'branches']);

        if (!$currentUser->isSuperAdmin()) {
            $query->where('company_id', $currentUser->company_id)
                  ->where('is_superadmin', false);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles = Role::orderBy('label')->get();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Formulário de novo usuário.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);
        $currentUser = Auth::user();

        $roles = Role::orderBy('label')->get();
        $branches = $currentUser->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('company_id', $currentUser->company_id)->active()->orderBy('name')->get();
        $companies = $currentUser->isSuperAdmin() ? Company::active()->orderBy('name')->get() : collect();

        return view('users.create', compact('roles', 'branches', 'companies'));
    }

    /**
     * Gravação do novo usuário com filiais e papéis por filial.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);
        $currentUser = Auth::user();

        $validated = $request->validated();
        $companyId = $currentUser->isSuperAdmin()
            ? $request->input('company_id')
            : $currentUser->company_id;

        $user = DB::transaction(function () use ($validated, $companyId, $request) {
            $user = User::create([
                'company_id'   => $companyId,
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'password'     => Hash::make($validated['password']),
                'phone'        => $validated['phone'] ?? null,
                'status'       => $validated['status'],
                'role_id'      => $validated['role_id'] ?? null,
                'all_branches' => $request->boolean('all_branches'),
            ]);

            // Sincroniza vínculos por filial com roles específicas
            if (!empty($validated['branches'])) {
                $syncData = [];
                foreach ($validated['branches'] as $branchItem) {
                    if (!empty($branchItem['branch_id'])) {
                        $syncData[$branchItem['branch_id']] = [
                            'role_id'    => $branchItem['role_id'] ?? null,
                            'is_default' => !empty($branchItem['is_default']),
                        ];
                    }
                }
                $user->branches()->sync($syncData);
            }

            return $user;
        });

        AuditService::log(
            action: 'create',
            description: "Usuário {$user->name} ({$user->email}) cadastrado",
            auditable: $user,
            newValues: ['name' => $user->name, 'email' => $user->email, 'status' => $user->status]
        );

        return redirect()->route('users.index')
            ->with('success', "Usuário '{$user->name}' cadastrado com sucesso!");
    }

    /**
     * Formulário de edição de usuário.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);
        $currentUser = Auth::user();

        $roles = Role::orderBy('label')->get();
        $branches = $currentUser->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('company_id', $currentUser->company_id)->active()->orderBy('name')->get();

        $user->load('branches');
        $userBranchesMap = $user->branches->keyBy('id');

        return view('users.edit', compact('user', 'roles', 'branches', 'userBranchesMap'));
    }

    /**
     * Atualização do usuário.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validated();
        $oldValues = ['name' => $user->name, 'email' => $user->email, 'status' => $user->status, 'role_id' => $user->role_id];

        DB::transaction(function () use ($user, $validated, $request) {
            $dataToUpdate = [
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'phone'        => $validated['phone'] ?? null,
                'status'       => $validated['status'],
                'role_id'      => $validated['role_id'] ?? null,
                'all_branches' => $request->boolean('all_branches'),
            ];

            if (!empty($validated['password'])) {
                $dataToUpdate['password'] = Hash::make($validated['password']);
            }

            $user->update($dataToUpdate);

            // Sincroniza vínculos por filial com roles
            $syncData = [];
            if (!empty($validated['branches'])) {
                foreach ($validated['branches'] as $branchItem) {
                    if (!empty($branchItem['branch_id'])) {
                        $syncData[$branchItem['branch_id']] = [
                            'role_id'    => $branchItem['role_id'] ?? null,
                            'is_default' => !empty($branchItem['is_default']),
                        ];
                    }
                }
            }
            $user->branches()->sync($syncData);
        });

        AuditService::log(
            action: 'update',
            description: "Usuário {$user->name} atualizado",
            auditable: $user,
            oldValues: $oldValues,
            newValues: ['name' => $user->name, 'email' => $user->email, 'status' => $user->status, 'role_id' => $user->role_id]
        );

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }
}
