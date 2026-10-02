<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Models\Brand;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BrandController extends Controller
{
    /**
     * Listagem de marcas cadastradas.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Brand::class);

        $query = Brand::withCount('products');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $brands = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('brands.index', compact('brands'));
    }

    /**
     * Cadastro de nova marca.
     */
    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $this->authorize('create', Brand::class);

        $validated = $request->validated();
        $validated['company_id'] = Auth::user()->company_id;
        $validated['status'] = $request->boolean('status', true);

        $brand = Brand::create($validated);

        AuditService::log(
            action: 'create',
            description: "Marca cadastrada: {$brand->name}",
            auditable: $brand,
            newValues: $brand->toArray()
        );

        return redirect()->route('brands.index')
            ->with('success', "Marca '{$brand->name}' cadastrada com sucesso!");
    }

    /**
     * Atualização de marca.
     */
    public function update(StoreBrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->authorize('update', $brand);

        $oldValues = $brand->toArray();
        $validated = $request->validated();
        $validated['status'] = $request->boolean('status', true);

        $brand->update($validated);

        AuditService::log(
            action: 'update',
            description: "Marca atualizada: {$brand->name}",
            auditable: $brand,
            oldValues: $oldValues,
            newValues: $brand->fresh()->toArray()
        );

        return redirect()->route('brands.index')
            ->with('success', "Marca '{$brand->name}' atualizada com sucesso!");
    }

    /**
     * Exclusão de marca.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('delete', $brand);

        if ($brand->products()->exists()) {
            return back()->with('error', 'Não é possível excluir uma marca que possui produtos vinculados.');
        }

        $name = $brand->name;
        $brand->delete();

        AuditService::log(
            action: 'delete',
            description: "Marca excluída: {$name}",
            auditable: $brand
        );

        return redirect()->route('brands.index')
            ->with('success', "Marca '{$name}' removida com sucesso.");
    }
}
