<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BranchController extends Controller
{
    /**
     * Listagem de filiais acessíveis.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $this->authorize('viewAny', Branch::class);

        $query = Branch::with(['company', 'warehouses', 'pointsOfSale']);

        if (!$user->isSuperAdmin()) {
            if ($user->all_branches && $user->company_id) {
                $query->where('company_id', $user->company_id);
            } else {
                $allowedIds = $user->branches()->pluck('branches.id')->toArray();
                $query->whereIn('id', $allowedIds);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $branches = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('branches.index', compact('branches'));
    }

    /**
     * Formulário de nova filial.
     */
    public function create(): View
    {
        $this->authorize('create', Branch::class);
        $companies = Auth::user()->isSuperAdmin()
            ? Company::active()->orderBy('name')->get()
            : Company::where('id', Auth::user()->company_id)->get();

        return view('branches.create', compact('companies'));
    }

    /**
     * Gravação de nova filial com auditoria e transação.
     */
    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $this->authorize('create', Branch::class);

        $validated = $request->validated();
        $validated['is_headquarters'] = $request->boolean('is_headquarters');
        $validated['status'] = $request->boolean('status', true);

        $branch = DB::transaction(function () use ($validated) {
            $branch = Branch::create($validated);

            // Se for matriz, remove status matriz de outras filiais da mesma empresa
            if ($branch->is_headquarters) {
                Branch::where('company_id', $branch->company_id)
                    ->where('id', '!=', $branch->id)
                    ->update(['is_headquarters' => false]);
            }

            // Cria automaticamente os depósitos essenciais para a nova filial
            $branch->warehouses()->create([
                'name'           => 'Área de Vendas - Loja',
                'code'           => ($branch->code ? $branch->code . '-' : '') . 'VND',
                'description'    => 'Piso de vendas / Loja física',
                'is_sales_floor' => true,
                'is_default'     => false,
                'status'         => true,
            ]);

            $branch->warehouses()->create([
                'name'           => 'Depósito Geral',
                'code'           => ($branch->code ? $branch->code . '-' : '') . 'GER',
                'description'    => 'Depósito principal de recebimento e armazenagem',
                'is_sales_floor' => false,
                'is_default'     => true,
                'status'         => true,
            ]);

            return $branch;
        });

        AuditService::log(
            action: 'create',
            description: "Filial '{$branch->name}' cadastrada com depósitos iniciais automáticos",
            auditable: $branch,
            newValues: $branch->toArray(),
            branchId: $branch->id
        );

        return redirect()->route('branches.show', $branch)
            ->with('success', "Filial '{$branch->name}' cadastrada com sucesso!");
    }

    /**
     * Detalhes da filial.
     */
    public function show(Branch $branch): View
    {
        $this->authorize('view', $branch);
        $branch->load(['company', 'warehouses', 'pointsOfSale', 'users']);

        return view('branches.show', compact('branch'));
    }

    /**
     * Formulário de edição.
     */
    public function edit(Branch $branch): View
    {
        $this->authorize('update', $branch);
        $companies = Auth::user()->isSuperAdmin()
            ? Company::active()->orderBy('name')->get()
            : Company::where('id', Auth::user()->company_id)->get();

        return view('branches.edit', compact('branch', 'companies'));
    }

    /**
     * Atualização da filial.
     */
    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->authorize('update', $branch);

        $oldValues = $branch->toArray();
        $validated = $request->validated();
        $validated['is_headquarters'] = $request->boolean('is_headquarters');
        $validated['status'] = $request->boolean('status', true);

        DB::transaction(function () use ($branch, $validated) {
            $branch->update($validated);

            if ($branch->is_headquarters) {
                Branch::where('company_id', $branch->company_id)
                    ->where('id', '!=', $branch->id)
                    ->update(['is_headquarters' => false]);
            }
        });

        AuditService::log(
            action: 'update',
            description: "Filial '{$branch->name}' atualizada",
            auditable: $branch,
            oldValues: $oldValues,
            newValues: $branch->fresh()->toArray(),
            branchId: $branch->id
        );

        return redirect()->route('branches.show', $branch)
            ->with('success', 'Dados da filial atualizados com sucesso!');
    }
}
