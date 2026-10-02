@extends('layouts.app')

@section('title', 'Estrutura Mercadológica & Categorias')

@section('content')
<div class="space-y-5" x-data="{
    createOpen: false,
    editOpen: false,
    selectedLevel: 'department',
    editCategory: { id: null, name: '', code: '', level: 'department', parent_id: '', description: '', status: 1 },
    openEdit(cat) {
        this.editCategory = {
            id: cat.id,
            name: cat.name,
            code: cat.code || '',
            level: cat.level,
            parent_id: cat.parent_id || '',
            description: cat.description || '',
            status: cat.status ? 1 : 0
        };
        this.editOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => 'Produtos', 'url' => route('products.index')], ['label' => 'Categorias']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Hierarquia Mercadológica</h1>
            <p class="text-xs text-slate-500 mt-0.5">Organização em 3 níveis: <strong>Departamento &rarr; Categoria &rarr; Subcategoria</strong></p>
        </div>

        @can('create', App\Models\Category::class)
            <x-button @click="createOpen = true" variant="primary" icon="plus">
                Nova Categoria / Nível
            </x-button>
        @endcan
    </div>

    <!-- Navegação por Nível & Filtros -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-3 shadow-2xs space-y-3">
        <!-- Abas de Níveis -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-xl w-fit text-xs font-medium">
            <a
                href="{{ route('categories.index', array_merge(request()->except('level', 'page'), [])) }}"
                class="px-3 py-1.5 rounded-lg transition-all {{ !request('level') ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
            >
                Todos os Níveis
            </a>
            <a
                href="{{ route('categories.index', array_merge(request()->except('page'), ['level' => 'department'])) }}"
                class="px-3 py-1.5 rounded-lg transition-all {{ request('level') === 'department' ? 'bg-white text-blue-700 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
            >
                1. Departamentos
            </a>
            <a
                href="{{ route('categories.index', array_merge(request()->except('page'), ['level' => 'category'])) }}"
                class="px-3 py-1.5 rounded-lg transition-all {{ request('level') === 'category' ? 'bg-white text-indigo-700 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
            >
                2. Categorias
            </a>
            <a
                href="{{ route('categories.index', array_merge(request()->except('page'), ['level' => 'subcategory'])) }}"
                class="px-3 py-1.5 rounded-lg transition-all {{ request('level') === 'subcategory' ? 'bg-white text-emerald-700 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
            >
                3. Subcategorias
            </a>
        </div>

        <form action="{{ route('categories.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            @if(request('level'))
                <input type="hidden" name="level" value="{{ request('level') }}">
            @endif

            <div class="flex-1 w-full">
                <x-input
                    name="search"
                    placeholder="Buscar por código ou nome do departamento/categoria..."
                    icon="search"
                    value="{{ request('search') }}"
                />
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <x-button type="submit" variant="secondary" size="md">
                    Filtrar
                </x-button>
                @if(request()->hasAny(['search', 'level']))
                    <x-button :href="route('categories.index')" variant="ghost" size="md">
                        Limpar
                    </x-button>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabela de Categorias -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">Código</th>
                        <th class="px-5 py-3.5">Nome</th>
                        <th class="px-5 py-3.5">Nível Estrutural</th>
                        <th class="px-5 py-3.5">Superior / Pai</th>
                        <th class="px-5 py-3.5 text-center">Subníveis</th>
                        <th class="px-5 py-3.5 text-center">Produtos</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-4 font-mono font-medium text-slate-600 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] border border-slate-200/60">
                                    {{ $category->code ?? 'N/D' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-900 text-sm">
                                <div class="flex items-center gap-2">
                                    @if($category->level === 'department')
                                        <x-icon name="folder" class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                    @elseif($category->level === 'category')
                                        <x-icon name="folder-git-2" class="w-4 h-4 text-indigo-500 flex-shrink-0" />
                                    @else
                                        <x-icon name="file-text" class="w-4 h-4 text-emerald-500 flex-shrink-0" />
                                    @endif
                                    <span>{{ $category->name }}</span>
                                </div>
                                @if($category->description)
                                    <div class="text-[11px] text-slate-400 font-normal mt-0.5 line-clamp-1 max-w-sm">{{ $category->description }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($category->level === 'department')
                                    <x-badge variant="info" size="sm">1. Departamento</x-badge>
                                @elseif($category->level === 'category')
                                    <x-badge variant="primary" size="sm">2. Categoria</x-badge>
                                @else
                                    <x-badge variant="success" size="sm">3. Subcategoria</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                                @if($category->parent)
                                    <span class="inline-flex items-center gap-1 font-medium text-slate-800">
                                        <span class="text-slate-400 text-xs">&rarr;</span>
                                        {{ $category->parent->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">Nível Raiz (Topo)</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs">
                                    <x-icon name="network" class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ $category->children->count() }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                                    <x-icon name="package" class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ $category->products_count }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <x-badge :variant="$category->status ? 'success' : 'danger'" size="sm" :dot="true">
                                    {{ $category->status ? 'Ativa' : 'Inativa' }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @can('update', $category)
                                        <button
                                            type="button"
                                            @click="openEdit({{ json_encode($category) }})"
                                            class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Editar"
                                        >
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </button>
                                    @endcan

                                    @can('delete', $category)
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                title="Excluir"
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
                            <td colspan="8" class="px-6 py-12">
                                <x-empty-state
                                    icon="folder-tree"
                                    title="Nenhuma categoria encontrada"
                                    description="Estruture os departamentos, categorias e subcategorias do supermercado."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Nova Categoria -->
    <div
        x-show="createOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs"
    >
        <div @click.away="createOpen = false" class="bg-white rounded-2xl overflow-hidden shadow-2xl w-full max-w-lg border border-slate-200/80">
            <div class="px-6 py-4.5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800">Novo Item Mercadológico</h3>
                <button type="button" @click="createOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <form action="{{ route('categories.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Nível Estrutural <span class="text-rose-500">*</span></label>
                    <select name="level" x-model="selectedLevel" required class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500">
                        <option value="department">1. Departamento (Raiz - Ex: Bebidas, Açougue)</option>
                        <option value="category">2. Categoria (Ex: Cervejas, Carnes Bovinas)</option>
                        <option value="subcategory">3. Subcategoria (Ex: Puro Malte, Cortes Nobres)</option>
                    </select>
                </div>

                <!-- Campo Pai (Departamento para Categoria) -->
                <div x-show="selectedLevel === 'category'" style="display: none;">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Departamento Pai <span class="text-rose-500">*</span></label>
                    <select name="parent_id" :disabled="selectedLevel !== 'category'" class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500">
                        <option value="">Selecione o departamento...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Campo Pai (Categoria para Subcategoria) -->
                <div x-show="selectedLevel === 'subcategory'" style="display: none;">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Categoria Pai <span class="text-rose-500">*</span></label>
                    <select name="parent_id" :disabled="selectedLevel !== 'subcategory'" class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500">
                        <option value="">Selecione a categoria mãe...</option>
                        @foreach($parentCategories as $parentCat)
                            <option value="{{ $parentCat->id }}">{{ $parentCat->name }} ({{ $parentCat->parent?->name ?? 'Geral' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-1">
                        <x-input name="code" label="Código" placeholder="Ex: BEB-01" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input name="name" label="Nome" placeholder="Ex: Bebidas Alcoólicas" required />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Descrição</label>
                    <textarea name="description" rows="2" class="w-full text-sm bg-white rounded-xl border border-slate-200/90 p-3 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500" placeholder="Observações opcionais..."></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="cat_status_new" name="status" value="1" checked class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500" />
                    <label for="cat_status_new" class="text-xs font-medium text-slate-700 cursor-pointer">Ativo no catálogo</label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <x-button type="button" @click="createOpen = false" variant="ghost">Cancelar</x-button>
                    <x-button type="submit" variant="primary">Salvar</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Categoria -->
    <div
        x-show="editOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs"
    >
        <div @click.away="editOpen = false" class="bg-white rounded-2xl overflow-hidden shadow-2xl w-full max-w-lg border border-slate-200/80">
            <div class="px-6 py-4.5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800">Editar Item Mercadológico</h3>
                <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <form :action="'{{ url('categories') }}/' + editCategory.id" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Nível Estrutural</label>
                    <select name="level" x-model="editCategory.level" required class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500">
                        <option value="department">1. Departamento (Raiz)</option>
                        <option value="category">2. Categoria</option>
                        <option value="subcategory">3. Subcategoria</option>
                    </select>
                </div>

                <div x-show="editCategory.level === 'category'" style="display: none;">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Departamento Pai</label>
                    <select name="parent_id" x-model="editCategory.parent_id" :disabled="editCategory.level !== 'category'" class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500">
                        <option value="">Selecione o departamento...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="editCategory.level === 'subcategory'" style="display: none;">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Categoria Pai</label>
                    <select name="parent_id" x-model="editCategory.parent_id" :disabled="editCategory.level !== 'subcategory'" class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500">
                        <option value="">Selecione a categoria mãe...</option>
                        @foreach($parentCategories as $parentCat)
                            <option value="{{ $parentCat->id }}">{{ $parentCat->name }} ({{ $parentCat->parent?->name ?? 'Geral' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Código</label>
                        <input type="text" name="code" x-model="editCategory.code" class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Nome <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editCategory.name" required class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Descrição</label>
                    <textarea name="description" x-model="editCategory.description" rows="2" class="w-full text-sm bg-white rounded-xl border border-slate-200/90 p-3 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="cat_status_edit" name="status" value="1" :checked="editCategory.status" class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500" />
                    <label for="cat_status_edit" class="text-xs font-medium text-slate-700 cursor-pointer">Ativo no catálogo</label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <x-button type="button" @click="editOpen = false" variant="ghost">Cancelar</x-button>
                    <x-button type="submit" variant="primary">Atualizar</x-button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
