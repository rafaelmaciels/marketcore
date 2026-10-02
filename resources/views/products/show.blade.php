@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="space-y-6">

    <!-- Header do Produto -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[
                ['label' => 'Produtos', 'url' => route('products.index')],
                ['label' => $product->name]
            ]" />
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $product->name }}</h1>
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
                @if($product->is_weighable)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold text-xs border border-emerald-100">
                        <x-icon name="scale" class="w-3.5 h-3.5 text-emerald-600" />
                        <span>Produto Pesável / Balança</span>
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                <span>SKU: <strong class="text-slate-800 font-mono">{{ $product->sku }}</strong></span>
                @if($product->internal_code)
                    <span>&bull; Cód. Interno: <strong class="text-slate-800 font-mono">{{ $product->internal_code }}</strong></span>
                @endif
                @if($product->brand)
                    <span>&bull; Marca: <strong class="text-slate-800">{{ $product->brand->name }}</strong></span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <x-button :href="route('products.index')" variant="secondary" icon="arrow-left">
                Voltar
            </x-button>
            @can('update', $product)
                <x-button :href="route('products.edit', $product)" variant="primary" icon="pencil">
                    Editar Produto
                </x-button>
            @endcan
        </div>
    </div>

    <!-- Grid Principal de Dados do Produto -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Coluna Esquerda: Especificações e Classificação -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Dados Cadastrais -->
            <x-card title="Classificação Mercadológica" subtitle="Hierarquia e unidade de venda">
                <div class="space-y-3.5 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Departamento:</span>
                        <span class="font-medium text-slate-900">{{ $product->department?->name ?? 'Geral' }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Categoria:</span>
                        <span class="font-medium text-slate-900">{{ $product->category?->name ?? '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Subcategoria:</span>
                        <span class="font-medium text-slate-900">{{ $product->subcategory?->name ?? '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Marca / Fabricante:</span>
                        <span class="font-medium text-slate-900">{{ $product->brand?->name ?? 'Não informada' }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Unidade de Venda Base:</span>
                        <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">{{ $product->unit_of_measure }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Venda por Balança:</span>
                        <span class="font-medium {{ $product->is_weighable ? 'text-emerald-700' : 'text-slate-600' }}">
                            {{ $product->is_weighable ? 'Sim (Fracionado)' : 'Não (Unidade Inteira)' }}
                        </span>
                    </div>
                </div>
            </x-card>

            <!-- Dimensões Físicas -->
            <x-card title="Pesos & Dimensões" subtitle="Especificações físicas para logística">
                <div class="space-y-3.5 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Peso Líquido:</span>
                        <span class="font-mono font-medium text-slate-800">{{ $product->net_weight ? number_format((float) $product->net_weight, 3, ',', '.') . ' kg' : '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Peso Bruto:</span>
                        <span class="font-mono font-medium text-slate-800">{{ $product->gross_weight ? number_format((float) $product->gross_weight, 3, ',', '.') . ' kg' : '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Volume:</span>
                        <span class="font-mono font-medium text-slate-800">{{ $product->volume ?: '-' }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Parâmetros de Estoque Teóricos -->
            <x-card title="Parâmetros de Estoque" subtitle="Limites de reposição e segurança (teóricos)">
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-1">Mínimo</span>
                        <span class="font-mono font-bold text-slate-800 text-sm">{{ number_format((float) $product->minimum_stock, 0, ',', '.') }}</span>
                        <span class="text-[10px] text-slate-400 block">{{ $product->unit_of_measure }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-1">Segurança</span>
                        <span class="font-mono font-bold text-amber-700 text-sm">{{ number_format((float) $product->safety_stock, 0, ',', '.') }}</span>
                        <span class="text-[10px] text-slate-400 block">{{ $product->unit_of_measure }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-1">Máximo</span>
                        <span class="font-mono font-bold text-slate-800 text-sm">{{ $product->maximum_stock ? number_format((float) $product->maximum_stock, 0, ',', '.') : '-' }}</span>
                        <span class="text-[10px] text-slate-400 block">{{ $product->unit_of_measure }}</span>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Coluna Direita: Códigos de Barras, Embalagens, Fiscal e Histórico -->
        <div class="space-y-6 lg:col-span-2">

            <!-- Descrições -->
            @if($product->short_description || $product->description)
                <x-card title="Descrições do Produto" subtitle="Informações comerciais e cupom fiscal">
                    <div class="space-y-3 text-xs">
                        @if($product->short_description)
                            <div>
                                <span class="text-slate-400 uppercase font-semibold text-[10px] block mb-0.5">Descrição para Cupom Fiscal (PDV):</span>
                                <div class="font-mono font-medium text-slate-800 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                    {{ $product->short_description }}
                                </div>
                            </div>
                        @endif

                        @if($product->description)
                            <div>
                                <span class="text-slate-400 uppercase font-semibold text-[10px] block mb-0.5">Descrição Completa / Ficha Técnica:</span>
                                <div class="text-slate-700 leading-relaxed bg-slate-50/50 p-3 rounded-xl border border-slate-100">
                                    {{ $product->description }}
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif

            <!-- Múltiplos Códigos de Barras -->
            <x-card title="Códigos de Barras Homologados" subtitle="{{ $product->barcodes->count() }} código(s) associado(s) para leitura no PDV e recebimento">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 border-b border-slate-100 text-[10px] uppercase font-semibold text-slate-400 tracking-wider">
                            <tr>
                                <th class="px-4 py-2.5">Padrão</th>
                                <th class="px-4 py-2.5">Código Numérico</th>
                                <th class="px-4 py-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($product->barcodes as $barcode)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-700 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[11px] font-mono">
                                            {{ $barcode->type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900 text-sm whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <x-icon name="barcode" class="w-4 h-4 text-slate-400" />
                                            <span>{{ $barcode->barcode }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @if($barcode->is_main)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold text-[10px] border border-blue-100">
                                                <x-icon name="star" class="w-3 h-3 text-blue-600 fill-blue-600" />
                                                <span>Principal</span>
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[11px]">Adicional</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-slate-400">
                                        Nenhum código de barras cadastrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- Sistema de Embalagens e Conversão de Unidades -->
            <x-card title="Embalagens & Fatores de Conversão" subtitle="Conversões automáticas entre unidades, pacotes e caixas">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 border-b border-slate-100 text-[10px] uppercase font-semibold text-slate-400 tracking-wider">
                            <tr>
                                <th class="px-4 py-2.5">Embalagem</th>
                                <th class="px-4 py-2.5">Sigla</th>
                                <th class="px-4 py-2.5 text-center">Fator Multiplicador</th>
                                <th class="px-4 py-2.5">Equivalência Base</th>
                                <th class="px-4 py-2.5">Código da Caixa</th>
                                <th class="px-4 py-2.5 text-center">Padrão Venda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($product->packages as $pkg)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">
                                        {{ $pkg->name }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-slate-600 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-[11px] font-semibold text-slate-700">
                                            {{ $pkg->unit_type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono font-bold text-blue-700 text-center whitespace-nowrap">
                                        {{ (float) $pkg->conversion_factor }}x
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap font-mono text-[11px]">
                                        1 {{ $pkg->unit_type }} = <strong>{{ (float) $pkg->conversion_factor }}</strong> {{ $product->unit_of_measure }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-slate-500 whitespace-nowrap text-[11px]">
                                        {{ $pkg->barcode ?: '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @if($pkg->is_default_sale)
                                            <x-badge variant="success" size="sm">Padrão</x-badge>
                                        @else
                                            <span class="text-slate-400 text-[11px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-slate-400">
                                        Nenhuma embalagem cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Conversor Interativo Prático -->
                <div class="mt-4 p-3.5 rounded-xl bg-indigo-50/60 border border-indigo-100" x-data="{
                    qtyInput: 1,
                    selectedPkgId: '{{ $product->packages->first()?->id ?? '' }}',
                    packages: {{ Js::from($product->packages) }},
                    unitBase: '{{ $product->unit_of_measure }}',
                    get currentFactor() {
                        const p = this.packages.find(item => item.id == this.selectedPkgId);
                        return p ? parseFloat(p.conversion_factor) : 1.0;
                    },
                    get totalBaseUnits() {
                        return (parseFloat(this.qtyInput || 0) * this.currentFactor).toFixed(3);
                    }
                }">
                    <span class="text-xs font-semibold text-indigo-900 block mb-2 flex items-center gap-1.5">
                        <x-icon name="calculator" class="w-4 h-4 text-indigo-600" />
                        <span>Simulador de Conversão de Estoque / Compra:</span>
                    </span>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <input
                            type="number"
                            x-model="qtyInput"
                            min="1"
                            class="w-20 h-8 rounded-lg border border-indigo-200 bg-white px-2 font-mono text-xs text-indigo-950 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <select
                            x-model="selectedPkgId"
                            class="h-8 rounded-lg border border-indigo-200 bg-white px-2.5 text-xs text-indigo-950 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <template x-for="p in packages" :key="p.id">
                                <option :value="p.id" x-text="p.name + ' (' + p.unit_type + ')'"></option>
                            </template>
                        </select>
                        <span class="font-bold text-indigo-900">=</span>
                        <div class="font-mono font-bold text-indigo-800 bg-white px-3 py-1.5 rounded-lg border border-indigo-200">
                            <span x-text="totalBaseUnits"></span> <span x-text="unitBase"></span>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Dados Fiscais & Tributários -->
            <x-card title="Parâmetros Fiscais & Tributários" subtitle="Regras para emissão de documentos fiscais eletrônicos">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">NCM</span>
                        <span class="font-mono font-bold text-slate-800">{{ $product->ncm ?: 'Não inf.' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">CEST</span>
                        <span class="font-mono font-bold text-slate-800">{{ $product->cest ?: 'Não inf.' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">Origem</span>
                        <span class="font-mono font-bold text-slate-800">{{ $product->origin }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">CST / CSOSN</span>
                        <span class="font-mono font-bold text-slate-800">{{ $product->cst_csosn ?: 'Não inf.' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-0.5">CFOP Padrão</span>
                        <span class="font-mono font-bold text-slate-800">{{ $product->cfop_default ?: 'Não inf.' }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Histórico & Trilha de Auditoria do Produto -->
            <x-card title="Histórico de Alterações & Auditoria" subtitle="Rastreabilidade completa de edições realizadas no item">
                <div class="space-y-4">
                    @forelse($history as $log)
                        <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    @php
                                        $actionBadge = match($log->action) {
                                            'create' => ['variant' => 'success', 'label' => 'Criação'],
                                            'update' => ['variant' => 'info', 'label' => 'Edição'],
                                            'delete' => ['variant' => 'danger', 'label' => 'Exclusão'],
                                            default => ['variant' => 'neutral', 'label' => $log->action]
                                        };
                                    @endphp
                                    <x-badge :variant="$actionBadge['variant']" size="sm">
                                        {{ $actionBadge['label'] }}
                                    </x-badge>
                                    <span class="font-semibold text-slate-800">
                                        {{ $log->user?->name ?? 'Sistema' }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-400">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }} ({{ $log->created_at->diffForHumans() }})
                                </span>
                            </div>

                            <p class="text-xs text-slate-600">{{ $log->description }}</p>

                            @if(!empty($log->old_values) && !empty($log->new_values))
                                <details class="mt-2 text-[11px] text-slate-500">
                                    <summary class="cursor-pointer hover:text-slate-800 font-medium select-none">
                                        Ver campos modificados
                                    </summary>
                                    <div class="mt-2 grid grid-cols-2 gap-2 p-2.5 rounded-lg bg-white border border-slate-200/80 font-mono text-[10px]">
                                        <div>
                                            <strong class="text-slate-400 block mb-1">Antes:</strong>
                                            <pre class="overflow-x-auto text-rose-700 whitespace-pre-wrap">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                        <div>
                                            <strong class="text-slate-400 block mb-1">Depois:</strong>
                                            <pre class="overflow-x-auto text-emerald-700 whitespace-pre-wrap">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>
                                </details>
                            @endif
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">
                            Nenhum registro de auditoria gravado para este produto.
                        </div>
                    @endforelse
                </div>
            </x-card>

        </div>

    </div>

</div>
@endsection
