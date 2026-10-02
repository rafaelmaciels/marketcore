@extends('layouts.app')

@section('title', 'PDVs e Frentes de Caixa')

@section('content')
<div class="space-y-5" x-data="{
    editingPos: null,
    editAction: '',
    openEdit(pos) {
        this.editingPos = pos;
        this.editAction = '{{ url('pos') }}/' + pos.id;
        $dispatch('open-modal', 'edit-pos-modal');
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => $branch->name, 'url' => route('branches.show', $branch)], ['label' => 'PDVs']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Frentes de Caixa (PDVs)</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Filial Selecionada: <strong class="text-slate-700 font-semibold">{{ $branch->name }}</strong> &bull; Total de Caixas: <code class="font-mono text-slate-700">{{ $pointsOfSale->total() }}</code>
            </p>
        </div>

        @can('create', App\Models\PointOfSale::class)
            <x-button @click="$dispatch('open-modal', 'new-pos-modal')" variant="primary" icon="plus">
                Novo Caixa / PDV
            </x-button>
        @endcan
    </div>

    <!-- Tabela de PDVs -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">Caixa</th>
                        <th class="px-5 py-3.5">Identificação</th>
                        <th class="px-5 py-3.5">Terminal / Estação</th>
                        <th class="px-5 py-3.5">IP Local</th>
                        <th class="px-5 py-3.5">Observações</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pointsOfSale as $pos)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white text-xs">
                                    #{{ str_pad($pos->number, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800 text-sm">
                                {{ $pos->name }}
                            </td>
                            <td class="px-5 py-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $pos->terminal_code ?? '-' }}
                            </td>
                            <td class="px-5 py-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $pos->ip_address ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-slate-500 max-w-xs truncate">
                                {{ $pos->notes ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @php
                                    $v = match($pos->status) {
                                        'active' => 'success',
                                        'maintenance' => 'warning',
                                        default => 'danger',
                                    };
                                @endphp
                                <x-badge :variant="$v" size="sm" :dot="true">
                                    {{ $pos->status === 'active' ? 'Ativo' : ($pos->status === 'maintenance' ? 'Manutenção' : 'Inativo') }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                @can('update', $pos)
                                    <x-button @click="openEdit({{ json_encode($pos) }})" variant="ghost" size="sm" title="Editar Caixa">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </x-button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state
                                    icon="scan-barcode"
                                    title="Nenhum PDV cadastrado"
                                    description="Cadastre caixas para registrar vendas na frente de loja."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pointsOfSale->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $pointsOfSale->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Novo PDV -->
    <x-modal name="new-pos-modal" title="Cadastrar Ponto de Venda (PDV)">
        <form action="{{ route('pos.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <x-input label="Nº do Caixa" name="number" type="number" min="1" max="999" placeholder="01" required />
                </div>
                <div class="col-span-2">
                    <x-input label="Identificação do Caixa" name="name" placeholder="Ex: Caixa 01 - Rápido" required />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input label="Terminal / Hardware" name="terminal_code" placeholder="PDV-01" />
                </div>
                <div>
                    <x-input label="IP Rede Local" name="ip_address" placeholder="192.168.1.100" />
                </div>
            </div>

            <x-select label="Status Operacional" name="status">
                <option value="active">Ativo (Liberado para vendas)</option>
                <option value="maintenance">Em Manutenção / Configuração</option>
                <option value="inactive">Inativo</option>
            </x-select>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Observações / Equipamentos Conectados</label>
                <textarea name="notes" rows="2" placeholder="Balança Toledo, impressora não fiscal, SAT..."
                          class="w-full text-sm bg-white rounded-xl border border-slate-200/90 p-3 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500 placeholder-slate-400"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <x-button @click="$dispatch('close-modal', 'new-pos-modal')" variant="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="primary">
                    Salvar PDV
                </x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Editar PDV -->
    <x-modal name="edit-pos-modal" title="Editar Caixa / PDV">
        <form :action="editAction" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Nº Caixa *</label>
                    <input type="number" name="number" x-model="editingPos ? editingPos.number : ''" min="1" max="999" required
                           class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Identificação *</label>
                    <input type="text" name="name" x-model="editingPos ? editingPos.name : ''" required
                           class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Terminal</label>
                    <input type="text" name="terminal_code" x-model="editingPos ? editingPos.terminal_code : ''"
                           class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">IP Rede</label>
                    <input type="text" name="ip_address" x-model="editingPos ? editingPos.ip_address : ''"
                           class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Status Operacional</label>
                <select name="status" x-model="editingPos ? editingPos.status : 'active'"
                        class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500">
                    <option value="active">Ativo (Liberado para vendas)</option>
                    <option value="maintenance">Em Manutenção / Configuração</option>
                    <option value="inactive">Inativo</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Observações</label>
                <textarea name="notes" rows="2" x-model="editingPos ? editingPos.notes : ''"
                          class="w-full text-sm bg-white rounded-xl border border-slate-200/90 p-3 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                <x-button @click="$dispatch('close-modal', 'edit-pos-modal')" variant="secondary">
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
