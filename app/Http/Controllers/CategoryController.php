<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Listagem hierárquica de Departamentos, Categorias e Subcategorias.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $level = $request->input('level');

        $query = Category::with(['parent', 'children'])->withCount('products');

        if ($level) {
            $query->where('level', $level);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('level')->orderBy('name')->paginate(20)->withQueryString();
        
        $departments = Category::departments()->active()->orderBy('name')->get();
        $parentCategories = Category::categories()->active()->orderBy('name')->get();

        return view('categories.index', compact('categories', 'departments', 'parentCategories'));
    }

    /**
     * Cadastro de categoria/departamento/subcategoria.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $validated = $request->validated();
        $validated['company_id'] = Auth::user()->company_id;
        $validated['status'] = $request->boolean('status', true);

        $category = Category::create($validated);

        AuditService::log(
            action: 'create',
            description: "Categoria/Departamento cadastrado: {$category->name} (Nível: {$category->level})",
            auditable: $category,
            newValues: $category->toArray()
        );

        return redirect()->route('categories.index')
            ->with('success', "Item '{$category->name}' cadastrado com sucesso!");
    }

    /**
     * Atualização de categoria.
     */
    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $oldValues = $category->toArray();
        $validated = $request->validated();
        $validated['status'] = $request->boolean('status', true);

        $category->update($validated);

        AuditService::log(
            action: 'update',
            description: "Categoria/Departamento atualizado: {$category->name}",
            auditable: $category,
            oldValues: $oldValues,
            newValues: $category->fresh()->toArray()
        );

        return redirect()->route('categories.index')
            ->with('success', "Item '{$category->name}' atualizado com sucesso!");
    }

    /**
     * Exclusão de categoria.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->children()->exists()) {
            return back()->with('error', 'Não é possível excluir uma categoria que possui subcategorias filhas vinculadas.');
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'Não é possível excluir uma categoria que possui produtos vinculados.');
        }

        $name = $category->name;
        $category->delete();

        AuditService::log(
            action: 'delete',
            description: "Categoria excluída: {$name}",
            auditable: $category
        );

        return redirect()->route('categories.index')
            ->with('success', "Item '{$name}' removido com sucesso.");
    }
}
