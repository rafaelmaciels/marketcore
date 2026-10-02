@extends('layouts.app')

@section('title', 'Catálogo de Produtos')

@section('content')
<div class="space-y-5">

    <!-- Header da Página -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => 'Produtos']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Catálogo de Produtos</h1>
            <p class="text-xs text-slate-500 mt-0.5">Gestão centralizada de itens, múltiplos códigos de barras, embalagens e dados fiscais</p>
        </div>

        <div class="flex items-center gap-2.5">
            <x-button :href="route('categories.index')" variant="secondary" icon="folder-tree">
                Categorias
            </x-button>
            <x-button :href="route('brands.index')" variant="secondary" icon="tag">
                Marcas
            </x-button>
            @can('create', App\Models\Product::class)
                <x-button :href="route('products.create')" variant="primary" icon="plus">
                    Novo Produto
                </x-button>
            @endcan
        </div>
    </div>

    <!-- Barra de Busca e Filtros Avançados -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs space-y-3" x-data="{ advanced: {{ request()->hasAny(['brand_id', 'department_id', 'category_id', 'is_weighable', 'status']) ? 'true' : 'false' }} }">
        <form action="{{ route('products.index') }}" method="GET" class="space-y-3">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex-1 w-full">
                    <x-input
                        name="search"
                        placeholder="Buscar por nome do produto, SKU, código interno ou código de barras (EAN)..."
                        icon="search"
                        value="{{ request('search') }}"
                    />
                </div>
                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <button
                        type="button"
                        @click="advanced = !advanced"
                        class="h-10 px-3.5 rounded-xl border border-slate-200/90 text-xs font-medium text-slate-600 hover:text-slate-900 hover:border-slate-300 transition-colors inline-flex items-center gap-1.5"
                    >
                        <x-icon name="sliders-horizontal" class="w-4 h-4 text-slate-400" />
                        <span>Filtros</span>
                    </button>
                    <x-button type="submit" variant="secondary" size="md">
                        Pesquisar
                    </x-button>
                    @if(request()->hasAny(['search', 'brand_id', 'department_id', 'category_id', 'is_weighable', 'status']))
                        <x-button :href="route('products.index')" variant="ghost" size="md">
                            Limpar
                        </x-button>
                    @endif
                </div>
            </div>

            <!-- Filtros Expansíveis -->
            <div x-show="advanced" x-transition class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-3 border-t border-slate-100">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Marca</label>
                    <x-select name="brand_id">
                        <option value="">Todas as Marcas</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Departamento</label>
                    <x-select name="department_id">
                        <option value="">Todos os Departamentos</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Categoria</label>
                    <x-select name="category_id">
                        <option value="">Todas as Categorias</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Balança / Pesável</label>
                    <x-select name="is_weighable">
                        <option value="">Todos</option>
                        <option value="1" {{ request('is_weighable') === '1' ? 'selected' : '' }}>Sim (Pesável)</option>
                        <option value="0" {{ request('is_weighable') === '0' ? 'selected' : '' }}>Não (Unitário)</option>
                    </x-select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Status</label>
                    <x-select name="status">
                        <option value="">Todos os Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                        <option value="discontinued" {{ request('status') === 'discontinued' ? 'selected' : '' }}>Descontinuado</option>
                    </x-select>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabela de Produtos -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">SKU / Cód. Interno</th>
                        <th class="px-5 py-3.5">Produto & Marca</th>
                        <th class="px-5 py-3.5">Mercadológico</th>
                        <th class="px-5 py-3.5">Cód. Barras (Principal)</th>
                        <th class="px-5 py-3.5 text-center">Unidade / Balança</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-mono font-semibold text-slate-900 text-sm">
                                    {{ $product->sku }}
                                </div>
                                @if($product->internal_code)
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                        Cód: {{ $product->internal_code }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900 text-sm hover:text-blue-600 transition-colors">
                                    <a href="{{ route('products.show', $product) }}">
                                        {{ $product->name }}
                                    </a>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if($product->brand)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                            <x-icon name="tag" class="w-3 h-3 text-slate-400" />
                                            {{ $product->brand->name }}
                                        </span>
                                    @endif
                                    @if($product->short_description)
                                        <span class="text-[11px] text-slate-400 truncate max-w-xs">{{ $product->short_description }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                <div class="font-medium text-slate-800">
                                    {{ $product->department?->name ?? 'Geral' }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $product->category?->name ?? '-' }}
                                    @if($product->subcategory)
                                        &rsaquo; {{ $product->subcategory->name }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($product->mainBarcode)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 border border-slate-200/80 font-mono text-xs text-slate-800">
                                        <x-icon name="barcode" class="w-3.5 h-3.5 text-slate-400" />
                                        <span>{{ $product->mainBarcode->barcode }}</span>
                                    </div>
                                    @if($product->barcodes->count() > 1)
                                        <span class="text-[10px] text-slate-400 font-medium ml-1">
                                            +{{ $product->barcodes->count() - 1 }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-[11px] italic">Sem código</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold text-[11px] border border-blue-100">
                                        {{ $product->unit_of_measure }}
                                    </span>
                                    @if($product->is_weighable)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-medium text-[11px] border border-emerald-100" title="Produto Pesável na Balança">
                                            <x-icon name="scale" class="w-3 h-3 text-emerald-600" />
                                            <span>Pesável</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @php
                                    $statusVariant = match($product->status) {
                                        'active' => 'success',
                                        'inactive' => 'danger',
                                        'discontinued' => 'warning',
                                        default => 'neutral'
                                    };
                                    $statusLabel = match($product->status) {
                                        'active' => 'Ativo',
                                        'inactive' => 'Inativo',
                                        'discontinued' => 'Descontinuado',
                                        default => $product->status
                                    };
                                @endphp
                                <x-badge :variant="$statusVariant" size="sm" :dot="true">
                                    {{ $statusLabel }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('products.show', $product) }}"
                                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
                                        title="Ver Detalhes"
                                    >
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </a>

                                    @can('update', $product)
                                        <a
                                            href="{{ route('products.edit', $product) }}"
                                            class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Editar Produto"
                                        >
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </a>
                                    @endcan

                                    @can('delete', $product)
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o produto {{ $product->name }}?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                title="Excluir Produto"
                                            >
                                                <x-icon name="trash-2" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12">
                                <x-empty-state
                                    icon="package-search"
                                    title="Nenhum produto encontrado"
                                    description="Cadastre seu primeiro produto para começar a estruturar o catálogo do supermercado."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
