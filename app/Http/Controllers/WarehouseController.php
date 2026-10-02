<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    /**
     * Listagem dos depósitos da filial ativa.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Warehouse::class);

        $activeBranchId = session('active_branch_id');
        $branch = Branch::findOrFail($activeBranchId);

        $query = Warehouse::where('branch_id', $branch->id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->orderByDesc('is_default')
            ->orderByDesc('is_sales_floor')
            ->orderBy('name')
            ->paginate(15);

        return view('warehouses.index', compact('warehouses', 'branch'));
    }

    /**
     * Cadastro de novo depósito na filial ativa.
     */
    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $this->authorize('create', Warehouse::class);

        $activeBranchId = session('active_branch_id');
        $branch = Branch::findOrFail($activeBranchId);

        $validated = $request->validated();
        $validated['branch_id'] = $branch->id;
        $validated['is_sales_floor'] = $request->boolean('is_sales_floor');
        $validated['is_default'] = $request->boolean('is_default');
        $validated['status'] = $request->boolean('status', true);

        $warehouse = DB::transaction(function () use ($branch, $validated) {
            // Se for marcado como default, remove de outros da mesma filial
            if ($validated['is_default']) {
                Warehouse::where('branch_id', $branch->id)->update(['is_default' => false]);
            }

            // Se for área de vendas, remove de outros da mesma filial se houver
            if ($validated['is_sales_floor']) {
                Warehouse::where('branch_id', $branch->id)->update(['is_sales_floor' => false]);
            }

            return Warehouse::create($validated);
        });

        AuditService::log(
            action: 'create',
            description: "Depósito '{$warehouse->name}' criado na filial '{$branch->name}'",
            auditable: $warehouse,
            newValues: $warehouse->toArray(),
            branchId: $branch->id
        );

        return redirect()->route('warehouses.index')
            ->with('success', "Depósito '{$warehouse->name}' cadastrado com sucesso!");
    }

    /**
     * Atualização do depósito.
     */
    public function update(StoreWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('update', $warehouse);

        // Validação de isolamento: pertence à filial ativa?
        if ($warehouse->branch_id !== (int) session('active_branch_id')) {
            abort(403, 'Ação proibida para depósitos de outra filial.');
        }

        $oldValues = $warehouse->toArray();
        $validated = $request->validated();
        $validated['is_sales_floor'] = $request->boolean('is_sales_floor');
        $validated['is_default'] = $request->boolean('is_default');
        $validated['status'] = $request->boolean('status', true);

        DB::transaction(function () use ($warehouse, $validated) {
            if ($validated['is_default']) {
                Warehouse::where('branch_id', $warehouse->branch_id)
                    ->where('id', '!=', $warehouse->id)
                    ->update(['is_default' => false]);
            }

            if ($validated['is_sales_floor']) {
                Warehouse::where('branch_id', $warehouse->branch_id)
                    ->where('id', '!=', $warehouse->id)
                    ->update(['is_sales_floor' => false]);
            }

            $warehouse->update($validated);
        });

        AuditService::log(
            action: 'update',
            description: "Depósito '{$warehouse->name}' atualizado",
            auditable: $warehouse,
            oldValues: $oldValues,
            newValues: $warehouse->fresh()->toArray(),
            branchId: $warehouse->branch_id
        );

        return redirect()->route('warehouses.index')
            ->with('success', 'Depósito atualizado com sucesso!');
    }
}
