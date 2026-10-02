<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductPackage;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Listagem de produtos com filtros e pesquisa avançada.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with(['brand', 'department', 'category', 'mainBarcode', 'defaultPackage']);

        // Busca textual (Nome, SKU, Código Interno ou Código de Barras)
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filtro por Marca
        if ($brandId = $request->input('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        // Filtro por Departamento
        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        // Filtro por Categoria
        if ($catId = $request->input('category_id')) {
            $query->where('category_id', $catId);
        }

        // Filtro por Pesável (Balança)
        if ($request->filled('is_weighable')) {
            $query->where('is_weighable', $request->boolean('is_weighable'));
        }

        // Filtro por Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $products = $query->latest('id')->paginate(15)->withQueryString();

        $brands = Brand::active()->orderBy('name')->get();
        $departments = Category::departments()->active()->orderBy('name')->get();
        $categories = Category::categories()->active()->orderBy('name')->get();

        return view('products.index', compact('products', 'brands', 'departments', 'categories'));
    }

    /**
     * Formulário de novo produto.
     */
    public function create(): View
    {
        $this->authorize('create', Product::class);

        $brands = Brand::active()->orderBy('name')->get();
        $departments = Category::departments()->active()->orderBy('name')->get();
        $categories = Category::categories()->active()->orderBy('name')->get();
        $subcategories = Category::subcategories()->active()->orderBy('name')->get();

        return view('products.create', compact('brands', 'departments', 'categories', 'subcategories'));
    }

    /**
     * Gravação do produto com múltiplos códigos de barras e embalagens.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $validated = $request->validated();
        $validated['company_id'] = Auth::user()->company_id;
        $validated['is_weighable'] = $request->boolean('is_weighable');

        $product = DB::transaction(function () use ($validated, $request) {
            $product = Product::create($validated);

            // Grava Códigos de Barras
            if (!empty($validated['barcodes'])) {
                foreach ($validated['barcodes'] as $bIndex => $bItem) {
                    if (!empty($bItem['barcode'])) {
                        $product->barcodes()->create([
                            'barcode' => trim($bItem['barcode']),
                            'type'    => $bItem['type'] ?? ProductBarcode::TYPE_EAN13,
                            'is_main' => !empty($bItem['is_main']) || $bIndex === 0,
                        ]);
                    }
                }
            }

            // Grava Embalagens com Fator de Conversão
            if (!empty($validated['packages'])) {
                foreach ($validated['packages'] as $pIndex => $pItem) {
                    if (!empty($pItem['name'])) {
                        $product->packages()->create([
                            'name'              => trim($pItem['name']),
                            'unit_type'         => $pItem['unit_type'] ?? 'UN',
                            'conversion_factor' => (float) ($pItem['conversion_factor'] ?? 1.0),
                            'barcode'           => !empty($pItem['barcode']) ? trim($pItem['barcode']) : null,
                            'is_default_sale'   => !empty($pItem['is_default_sale']) || $pIndex === 0,
                            'status'            => true,
                        ]);
                    }
                }
            } else {
                // Embalagem unitária padrão automática
                $product->packages()->create([
                    'name'              => 'Unidade',
                    'unit_type'         => $product->unit_of_measure ?: 'UN',
                    'conversion_factor' => 1.0,
                    'is_default_sale'   => true,
                    'status'            => true,
                ]);
            }

            return $product;
        });

        AuditService::log(
            action: 'create',
            description: "Produto cadastrado: {$product->name} (SKU: {$product->sku})",
            auditable: $product,
            newValues: $product->toArray()
        );

        return redirect()->route('products.show', $product)
            ->with('success', "Produto '{$product->name}' cadastrado com sucesso!");
    }

    /**
     * Detalhes completos do produto e histórico.
     */
    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $product->load(['brand', 'department', 'category', 'subcategory', 'barcodes', 'packages']);

        // Histórico de alterações do produto registrado na auditoria
        $history = AuditLog::with('user')
            ->where('auditable_type', Product::class)
            ->where('auditable_id', $product->id)
            ->latest('created_at')
            ->get();

        return view('products.show', compact('product', 'history'));
    }

    /**
     * Formulário de edição.
     */
    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load(['barcodes', 'packages']);
        $brands = Brand::active()->orderBy('name')->get();
        $departments = Category::departments()->active()->orderBy('name')->get();
        $categories = Category::categories()->active()->orderBy('name')->get();
        $subcategories = Category::subcategories()->active()->orderBy('name')->get();

        return view('products.edit', compact('product', 'brands', 'departments', 'categories', 'subcategories'));
    }

    /**
     * Atualização do produto, códigos de barras e embalagens.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $oldValues = $product->toArray();
        $validated = $request->validated();
        $validated['is_weighable'] = $request->boolean('is_weighable');

        DB::transaction(function () use ($product, $validated) {
            $product->update($validated);

            // Sincroniza Códigos de Barras
            if (isset($validated['barcodes'])) {
                $product->barcodes()->delete();
                foreach ($validated['barcodes'] as $bIndex => $bItem) {
                    if (!empty($bItem['barcode'])) {
                        $product->barcodes()->create([
                            'barcode' => trim($bItem['barcode']),
                            'type'    => $bItem['type'] ?? ProductBarcode::TYPE_EAN13,
                            'is_main' => !empty($bItem['is_main']) || $bIndex === 0,
                        ]);
                    }
                }
            }

            // Sincroniza Embalagens
            if (isset($validated['packages'])) {
                $product->packages()->delete();
                foreach ($validated['packages'] as $pIndex => $pItem) {
                    if (!empty($pItem['name'])) {
                        $product->packages()->create([
                            'name'              => trim($pItem['name']),
                            'unit_type'         => $pItem['unit_type'] ?? 'UN',
                            'conversion_factor' => (float) ($pItem['conversion_factor'] ?? 1.0),
                            'barcode'           => !empty($pItem['barcode']) ? trim($pItem['barcode']) : null,
                            'is_default_sale'   => !empty($pItem['is_default_sale']) || $pIndex === 0,
                            'status'            => true,
                        ]);
                    }
                }
            }
        });

        AuditService::log(
            action: 'update',
            description: "Produto atualizado: {$product->name} (SKU: {$product->sku})",
            auditable: $product,
            oldValues: $oldValues,
            newValues: $product->fresh()->toArray()
        );

        return redirect()->route('products.show', $product)
            ->with('success', "Produto '{$product->name}' atualizado com sucesso!");
    }

    /**
     * Exclusão lógica (soft delete) do produto.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $productName = $product->name;
        $product->delete();

        AuditService::log(
            action: 'delete',
            description: "Produto excluído: {$productName} (SKU: {$product->sku})",
            auditable: $product
        );

        return redirect()->route('products.index')
            ->with('success', "Produto '{$productName}' removido com sucesso.");
    }
}
