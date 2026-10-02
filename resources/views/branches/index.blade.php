@extends('layouts.app')

@section('title', 'Filiais e Lojas')

@section('content')
<div class="space-y-5">

    <!-- Header da Página -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => 'Filiais']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Filiais & Unidades de Negócio</h1>
            <p class="text-xs text-slate-500 mt-0.5">Gestão centralizada de lojas físicas, matriz e centros de distribuição</p>
        </div>

        @can('create', App\Models\Branch::class)
            <x-button :href="route('branches.create')" variant="primary" icon="plus">
                Nova Filial
            </x-button>
        @endcan
    </div>

    <!-- Barra de Pesquisa e Filtros -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-3 shadow-2xs">
        <form action="{{ route('branches.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex-1 w-full">
                <x-input
                    name="search"
                    placeholder="Buscar por nome da filial, código, CNPJ ou cidade..."
                    icon="search"
                    value="{{ request('search') }}"
                />
            </div>
            <div class="flex items-center gap-2 self-end sm:self-auto">
                <x-button type="submit" variant="secondary" size="md">
                    Filtrar
                </x-button>
                @if(request('search'))
                    <x-button :href="route('branches.index')" variant="ghost" size="md">
                        Limpar
                    </x-button>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabela de Filiais -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">Código</th>
                        <th class="px-5 py-3.5">Filial / Loja</th>
                        <th class="px-5 py-3.5">CNPJ</th>
                        <th class="px-5 py-3.5">Localização</th>
                        <th class="px-5 py-3.5 text-center">Depósitos</th>
                        <th class="px-5 py-3.5 text-center">PDVs</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($branches as $branch)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-4 font-mono font-medium text-slate-500 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] border border-slate-200/60">
                                    {{ $branch->code ?? 'N/D' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-slate-900 text-sm">{{ $branch->name }}</span>
                                    @if($branch->is_headquarters)
                                        <x-badge variant="info" size="sm">Matriz</x-badge>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $branch->company?->name }}</div>
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-600 whitespace-nowrap">
                                {{ $branch->cnpj ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                                {{ $branch->city ? "{$branch->city}/{$branch->state}" : 'Não cadastrado' }}
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs">
                                    <x-icon name="boxes" class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ $branch->warehouses->count() }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs">
                                    <x-icon name="scan-barcode" class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ $branch->pointsOfSale->count() }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <x-badge :variant="$branch->status ? 'success' : 'danger'" size="sm" :dot="true">
                                    {{ $branch->status ? 'Ativa' : 'Inativa' }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <x-button :href="route('branches.show', $branch)" variant="ghost" size="sm" title="Visualizar">
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </x-button>
                                    @can('update', $branch)
                                        <x-button :href="route('branches.edit', $branch)" variant="ghost" size="sm" title="Editar">
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </x-button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state
                                    icon="store"
                                    title="Nenhuma filial cadastrada"
                                    description="Não encontramos unidades com os termos pesquisados."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($branches->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $branches->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
