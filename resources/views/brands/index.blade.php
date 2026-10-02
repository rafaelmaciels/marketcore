@extends('layouts.app')

@section('title', 'Marcas de Produtos')

@section('content')
<div class="space-y-5" x-data="{
    createOpen: false,
    editOpen: false,
    editBrand: { id: null, name: '', code: '', description: '', status: 1 },
    openEdit(brand) {
        this.editBrand = { ...brand };
        this.editOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => 'Produtos', 'url' => route('products.index')], ['label' => 'Marcas']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Marcas de Produtos</h1>
            <p class="text-xs text-slate-500 mt-0.5">Gerencie os fabricantes e marcas dos produtos comercializados</p>
        </div>

        @can('create', App\Models\Brand::class)
            <x-button @click="createOpen = true" variant="primary" icon="plus">
                Nova Marca
            </x-button>
        @endcan
    </div>

    <!-- Barra de Filtros e Pesquisa -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-3 shadow-2xs">
        <form action="{{ route('brands.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex-1 w-full">
                <x-input
                    name="search"
                    placeholder="Buscar por nome, código ou descrição da marca..."
                    icon="search"
                    value="{{ request('search') }}"
                />
            </div>

            <div class="w-full sm:w-48">
                <x-select name="status">
                    <option value="">Todos os status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Ativas</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inativas</option>
                </x-select>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <x-button type="submit" variant="secondary" size="md">
                    Filtrar
                </x-button>
                @if(request()->hasAny(['search', 'status']))
                    <x-button :href="route('brands.index')" variant="ghost" size="md">
                        Limpar
                    </x-button>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabela de Marcas -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">Marca</th>
                        <th class="px-5 py-3.5">Código</th>
                        <th class="px-5 py-3.5">Descrição</th>
                        <th class="px-5 py-3.5 text-center">Qtd. Produtos</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($brands as $brand)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center font-bold text-blue-700 text-xs">
                                        {{ strtoupper(substr($brand->name, 0, 2)) }}
                                    </div>
                                    <div class="font-semibold text-slate-900 text-sm">{{ $brand->name }}</div>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-500 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] border border-slate-200/60">
                                    {{ $brand->code ?: '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <span class="line-clamp-1 max-w-sm">{{ $brand->description ?: '-' }}</span>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                                    <x-icon name="package" class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ $brand->products_count }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <x-badge :variant="$brand->status ? 'success' : 'danger'" size="sm" :dot="true">
                                    {{ $brand->status ? 'Ativa' : 'Inativa' }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @can('update', $brand)
                                        <button
                                            type="button"
                                            @click="openEdit({{ json_encode($brand) }})"
                                            class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Editar Marca"
                                        >
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </button>
                                    @endcan

                                    @can('delete', $brand)
                                        <form action="{{ route('brands.destroy', $brand) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta marca?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                title="Excluir Marca"
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
                            <td colspan="6" class="px-6 py-12">
                                <x-empty-state
                                    icon="tag"
                                    title="Nenhuma marca cadastrada"
                                    description="Cadastre marcas para organizar melhor os produtos do seu catálogo."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($brands->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $brands->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Nova Marca -->
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
                <h3 class="text-base font-semibold text-slate-800">Nova Marca</h3>
                <button type="button" @click="createOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <form action="{{ route('brands.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <x-input name="name" label="Nome da Marca" placeholder="Ex: Coca-Cola, Nestlé..." required />
                    </div>
                    <div>
                        <x-input name="code" label="Código / Sigla" placeholder="Ex: COCA" />
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Descrição / Apresentação</label>
                    <textarea name="description" rows="3" class="w-full text-sm bg-white rounded-xl border border-slate-200/90 p-3 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500 focus:ring-blue-500/15" placeholder="Breve resumo sobre o fabricante ou linha..."></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="brand_status_new" name="status" value="1" checked class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500" />
                    <label for="brand_status_new" class="text-xs font-medium text-slate-700 cursor-pointer">Marca ativa no sistema</label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <x-button type="button" @click="createOpen = false" variant="ghost">Cancelar</x-button>
                    <x-button type="submit" variant="primary">Salvar Marca</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Marca -->
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
                <h3 class="text-base font-semibold text-slate-800">Editar Marca</h3>
                <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <form :action="'{{ url('brands') }}/' + editBrand.id" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Nome da Marca <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editBrand.name" required class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500 focus:ring-blue-500/15" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Código / Sigla</label>
                        <input type="text" name="code" x-model="editBrand.code" class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500 focus:ring-blue-500/15" />
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Descrição / Apresentação</label>
                    <textarea name="description" x-model="editBrand.description" rows="3" class="w-full text-sm bg-white rounded-xl border border-slate-200/90 p-3 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500 focus:ring-blue-500/15"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="brand_status_edit" name="status" value="1" :checked="editBrand.status" class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500" />
                    <label for="brand_status_edit" class="text-xs font-medium text-slate-700 cursor-pointer">Marca ativa no sistema</label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <x-button type="button" @click="editOpen = false" variant="ghost">Cancelar</x-button>
                    <x-button type="submit" variant="primary">Atualizar Marca</x-button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
