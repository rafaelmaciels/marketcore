<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePointOfSaleRequest;
use App\Models\Branch;
use App\Models\PointOfSale;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointOfSaleController extends Controller
{
    /**
     * Listagem dos PDVs da filial ativa.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PointOfSale::class);

        $activeBranchId = session('active_branch_id');
        $branch = Branch::findOrFail($activeBranchId);

        $query = PointOfSale::where('branch_id', $branch->id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('number', 'like', "%{$search}%")
                  ->orWhere('terminal_code', 'like', "%{$search}%");
            });
        }

        $pointsOfSale = $query->orderBy('number')->paginate(15);

        return view('pos.index', compact('pointsOfSale', 'branch'));
    }

    /**
     * Cadastro de novo PDV na filial.
     */
    public function store(StorePointOfSaleRequest $request): RedirectResponse
    {
        $this->authorize('create', PointOfSale::class);

        $activeBranchId = session('active_branch_id');
        $branch = Branch::findOrFail($activeBranchId);

        // Verifica unicidade de número na filial
        $exists = PointOfSale::where('branch_id', $branch->id)
            ->where('number', $request->input('number'))
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'number' => 'Já existe um PDV/Caixa com este número nesta filial.',
            ]);
        }

        $validated = $request->validated();
        $validated['branch_id'] = $branch->id;

        $pos = PointOfSale::create($validated);

        AuditService::log(
            action: 'create',
            description: "PDV #{$pos->number} ('{$pos->name}') cadastrado na filial '{$branch->name}'",
            auditable: $pos,
            newValues: $pos->toArray(),
            branchId: $branch->id
        );

        return redirect()->route('pos.index')
            ->with('success', "PDV Caixa {$pos->number} cadastrado com sucesso!");
    }

    /**
     * Atualização do PDV.
     */
    public function update(StorePointOfSaleRequest $request, PointOfSale $pointOfSale): RedirectResponse
    {
        $this->authorize('update', $pointOfSale);

        if ($pointOfSale->branch_id !== (int) session('active_branch_id')) {
            abort(403, 'Ação proibida para PDV de outra filial.');
        }

        // Verifica conflito de número
        $exists = PointOfSale::where('branch_id', $pointOfSale->branch_id)
            ->where('number', $request->input('number'))
            ->where('id', '!=', $pointOfSale->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'number' => 'Já existe outro PDV/Caixa com este número nesta filial.',
            ]);
        }

        $oldValues = $pointOfSale->toArray();
        $pointOfSale->update($request->validated());

        AuditService::log(
            action: 'update',
            description: "PDV #{$pointOfSale->number} atualizado",
            auditable: $pointOfSale,
            oldValues: $oldValues,
            newValues: $pointOfSale->fresh()->toArray(),
            branchId: $pointOfSale->branch_id
        );

        return redirect()->route('pos.index')
            ->with('success', 'PDV atualizado com sucesso!');
    }
}
