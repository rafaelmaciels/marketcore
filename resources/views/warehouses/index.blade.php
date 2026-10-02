@extends('layouts.app')

@section('title', 'Depósitos de Estoque')

@section('content')
<div class="space-y-5" x-data="{
    editingWarehouse: null,
    editAction: '',
    openEdit(wh) {
        this.editingWarehouse = wh;
        this.editAction = '{{ url('warehouses') }}/' + wh.id;
        $dispatch('open-modal', 'edit-warehouse-modal');
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => $branch->name, 'url' => route('branches.show', $branch)], ['label' => 'Depósitos']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Depósitos & Locais de Estoque</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Filial Selecionada: <strong class="text-slate-700 font-semibold">{{ $branch->name }}</strong> &bull; Código: <code class="font-mono text-slate-700">{{ $branch->code ?? 'N/D' }}</code>
            </p>
        </div>

        @can('create', App\Models\Warehouse::class)
            <x-button @click="$dispatch('open-modal', 'new-warehouse-modal')" variant="primary" icon="plus">
                Novo Depósito
            </x-button>
        @endcan
    </div>

    <!-- Tabela de Depósitos -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">Código</th>
                        <th class="px-5 py-3.5">Nome do Depósito</th>
                        <th class="px-5 py-3.5">Descrição / Localização</th>
                        <th class="px-5 py-3.5">Finalidade Operacional</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($warehouses as $wh)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-4 font-mono font-medium text-slate-500 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] border border-slate-200/60">
                                    {{ $wh->code ?? 'N/D' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-semibold text-slate-900 text-sm">{{ $wh->name }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-500 max-w-xs truncate">
                                {{ $wh->description ?? '-' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($wh->is_default)
                                        <x-badge variant="info" size="sm">
                                            <x-icon name="check-circle" class="w-3 h-3 mr-1" />
                                            Recebimento Padrão
                                        </x-badge>
                                    @endif
                                    @if($wh->is_sales_floor)
                                        <x-badge variant="success" size="sm">
                                            <x-icon name="store" class="w-3 h-3 mr-1" />
                                            Área de Vendas / Loja
                                        </x-badge>
                                    @endif
                                    @if(!$wh->is_default && !$wh->is_sales_floor)
                                        <span class="text-slate-400 text-xs">Estoque Geral</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <x-badge :variant="$wh->status ? 'success' : 'danger'" size="sm" :dot="true">
                                    {{ $wh->status ? 'Ativo' : 'Inativo' }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                @can('update', $wh)
                                    <x-button @click="openEdit({{ json_encode($wh) }})" variant="ghost" size="sm" title="Editar Depósito">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </x-button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state
                                    icon="boxes"
                                    title="Nenhum depósito cadastrado"
                                    description="Esta filial ainda não possui locais de armazenamento registrados."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($warehouses->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $warehouses->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Novo Depósito -->
    <x-modal name="new-warehouse-modal" title="Cadastrar Novo Depósito">
        <form action="{{ route('warehouses.store') }}" method="POST" class="space-y-4">
            @csrf

            <x-input label="Nome do Depósito" name="name" placeholder="Ex: Galpão A - Alimentos Secos" required />
            <x-input label="Código Interno" name="code" placeholder="DEP-01" />

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Descrição / Localização Física</label>
                <textarea name="description" rows="2" placeholder="Informações de acesso, temperatura ou capacidade..."
                          class="w-full text-sm bg-white rounded-xl border border-slate-200/90 p-3 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500 placeholder-slate-400"></textarea>
            </div>

            <div class="space-y-2 pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_sales_floor" value="1"
                           class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20">
                    <span class="text-xs text-slate-700 font-medium">Área de Vendas (Gôndolas de Frente de Loja)</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1"
                           class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20">
                    <span class="text-xs text-slate-700 font-medium">Depósito Padrão para Recebimento de Mercadorias</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="status" value="1" checked
                           class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20">
                    <span class="text-xs text-slate-700 font-medium">Depósito Ativo</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <x-button @click="$dispatch('close-modal', 'new-warehouse-modal')" variant="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="primary">
                    Salvar Depósito
                </x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Editar Depósito -->
    <x-modal name="edit-warehouse-modal" title="Editar Depósito">
        <form :action="editAction" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Nome do Depósito *</label>
                <input type="text" name="name" x-model="editingWarehouse ? editingWarehouse.name : ''" required
                       class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Código Interno</label>
                <input type="text" name="code" x-model="editingWarehouse ? editingWarehouse.code : ''"
                       class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Descrição</label>
                <textarea name="description" rows="2" x-model="editingWarehouse ? editingWarehouse.description : ''"
                          class="w-full text-sm bg-white rounded-xl border border-slate-200/90 p-3 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500"></textarea>
            </div>

            <div class="space-y-2 pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_sales_floor" value="1" :checked="editingWarehouse && editingWarehouse.is_sales_floor"
                           class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20">
                    <span class="text-xs text-slate-700 font-medium">Área de Vendas</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" :checked="editingWarehouse && editingWarehouse.is_default"
                           class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20">
                    <span class="text-xs text-slate-700 font-medium">Depósito Padrão de Recebimento</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="status" value="1" :checked="editingWarehouse && editingWarehouse.status"
                           class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20">
                    <span class="text-xs text-slate-700 font-medium">Depósito Ativo</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <x-button @click="$dispatch('close-modal', 'edit-warehouse-modal')" variant="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="primary">
                    Salvar Alterações
                </x-button>
            </div>
        </form>
    </x-modal>

</div>
@endsection
