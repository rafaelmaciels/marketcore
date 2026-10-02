@extends('layouts.app')

@section('title', 'Dados da Empresa')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => 'Empresa']]" />
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $company->name }}</h1>
                <x-badge variant="success" size="sm" :dot="true">Empresa Ativa</x-badge>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Visão consolidada da holding e rede de unidades</p>
        </div>

        <x-button :href="route('branches.index')" variant="secondary" iconRight="arrow-right">
            Ver Todas as Filiais
        </x-button>
    </div>

    <!-- Grid Institucional -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Informações Fiscais -->
        <x-card title="Dados Cadastrais" subtitle="Informações fiscais da empresa matriz" class="h-fit">
            <div class="space-y-3.5 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">Nome Fantasia:</span>
                    <span class="font-medium text-slate-800">{{ $company->trade_name ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">CNPJ Matriz:</span>
                    <span class="font-mono font-medium text-slate-800">{{ $company->cnpj }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">Inscrição Estadual:</span>
                    <span class="font-medium text-slate-800">{{ $company->ie ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">Telefone:</span>
                    <span class="text-slate-700">{{ $company->phone ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400">E-mail Corporativo:</span>
                    <span class="text-slate-700">{{ $company->email ?? '-' }}</span>
                </div>
            </div>
        </x-card>

        <!-- Unidades da Rede (2 colunas) -->
        <div class="lg:col-span-2">
            <x-card title="Unidades & Lojas da Rede" subtitle="{{ $company->branches->count() }} filiais e centros cadastrados">
                <div class="-mx-6 -my-6 overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-3">Filial</th>
                                <th class="px-6 py-3">Localidade</th>
                                <th class="px-6 py-3 text-center">Depósitos</th>
                                <th class="px-6 py-3 text-center">PDVs</th>
                                <th class="px-6 py-3 text-right">Tipo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($company->branches as $branch)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-900 text-sm">{{ $branch->name }}</div>
                                        <div class="font-mono text-[11px] text-slate-400">CNPJ: {{ $branch->cnpj }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                        {{ $branch->city }}/{{ $branch->state }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs">
                                            {{ $branch->warehouses->count() }} locais
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs">
                                            {{ $branch->pointsOfSale->count() }} caixas
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        @if($branch->is_headquarters)
                                            <x-badge variant="info" size="sm">Matriz</x-badge>
                                        @else
                                            <x-badge variant="neutral" size="sm">Filial</x-badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

    </div>

</div>
@endsection
