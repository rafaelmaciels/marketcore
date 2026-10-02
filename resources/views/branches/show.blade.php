@extends('layouts.app')

@section('title', $branch->name)

@section('content')
<div class="space-y-6">

    <!-- Header da Filial -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => 'Filiais', 'url' => route('branches.index')], ['label' => $branch->name]]" />
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $branch->name }}</h1>
                @if($branch->is_headquarters)
                    <x-badge variant="info" size="sm">Matriz</x-badge>
                @endif
                <x-badge :variant="$branch->status ? 'success' : 'danger'" size="sm" :dot="true">
                    {{ $branch->status ? 'Ativa' : 'Inativa' }}
                </x-badge>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                {{ $branch->company?->name }} &bull; Código Interno: <code class="text-slate-700 font-mono">{{ $branch->code ?? 'N/D' }}</code>
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <x-button :href="route('branches.index')" variant="secondary" icon="arrow-left">
                Voltar
            </x-button>
            @can('update', $branch)
                <x-button :href="route('branches.edit', $branch)" variant="primary" icon="pencil">
                    Editar Filial
                </x-button>
            @endcan
        </div>
    </div>

    <!-- Grid de Informações -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Informações Fiscais & Contato -->
        <x-card title="Dados Institucionais" subtitle="Identificação e localização" class="h-fit">
            <div class="space-y-3.5 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">CNPJ:</span>
                    <span class="font-mono font-medium text-slate-800">{{ $branch->cnpj ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">Inscrição Estadual:</span>
                    <span class="font-medium text-slate-800">{{ $branch->ie ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">Telefone:</span>
                    <span class="text-slate-700">{{ $branch->phone ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">E-mail:</span>
                    <span class="text-slate-700">{{ $branch->email ?? '-' }}</span>
                </div>

                <div class="pt-1">
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1">Endereço Completo:</span>
                    <span class="text-slate-800 leading-relaxed block">{{ $branch->formatted_address ?: 'Não cadastrado' }}</span>
                </div>
            </div>
        </x-card>

        <!-- Depósitos e PDVs (2 colunas) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Tabela Depósitos -->
            <x-card title="Depósitos da Filial" subtitle="{{ $branch->warehouses->count() }} locais de estoque configurados">
                <x-slot:action>
                    <x-button :href="route('warehouses.index')" variant="ghost" size="sm" iconRight="arrow-right">
                        Gerenciar Depósitos
                    </x-button>
                </x-slot:action>

                <div class="-mx-6 -my-6 overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 border-b border-slate-100 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-3">Código</th>
                                <th class="px-6 py-3">Nome</th>
                                <th class="px-6 py-3">Finalidade</th>
                                <th class="px-6 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($branch->warehouses as $wh)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-3 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        {{ $wh->code ?? 'N/D' }}
                                    </td>
                                    <td class="px-6 py-3 font-semibold text-slate-800">
                                        {{ $wh->name }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        @if($wh->is_sales_floor)
                                            <x-badge variant="info" size="sm">Área de Vendas</x-badge>
                                        @endif
                                        @if($wh->is_default)
                                            <x-badge variant="success" size="sm">Padrão Recebimento</x-badge>
                                        @endif
                                        @if(!$wh->is_sales_floor && !$wh->is_default)
                                            <span class="text-slate-400 text-xs">Armazenagem</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-right whitespace-nowrap">
                                        <x-badge :variant="$wh->status ? 'success' : 'danger'" size="sm" :dot="true">
                                            {{ $wh->status ? 'Ativo' : 'Inativo' }}
                                        </x-badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-slate-400">
                                        Nenhum depósito cadastrado nesta filial.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- Tabela PDVs -->
            <x-card title="Frentes de Caixa (PDVs)" subtitle="{{ $branch->pointsOfSale->count() }} caixas cadastrados">
                <x-slot:action>
                    <x-button :href="route('pos.index')" variant="ghost" size="sm" iconRight="arrow-right">
                        Gerenciar PDVs
                    </x-button>
                </x-slot:action>

                <div class="-mx-6 -my-6 overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 border-b border-slate-100 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-3">Número</th>
                                <th class="px-6 py-3">Identificação</th>
                                <th class="px-6 py-3">Terminal</th>
                                <th class="px-6 py-3">IP Rede</th>
                                <th class="px-6 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($branch->pointsOfSale as $pos)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-3 font-mono font-bold text-slate-800 whitespace-nowrap">
                                        #{{ str_pad($pos->number, 2, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="px-6 py-3 font-medium text-slate-800">
                                        {{ $pos->name }}
                                    </td>
                                    <td class="px-6 py-3 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        {{ $pos->terminal_code ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        {{ $pos->ip_address ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-right whitespace-nowrap">
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-slate-400">
                                        Nenhum PDV cadastrado nesta filial.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

        </div>

    </div>

</div>
@endsection
