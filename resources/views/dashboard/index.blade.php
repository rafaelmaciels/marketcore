@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Cabeçalho da Página com Breadcrumbs Integrados -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
    <div>
        <x-breadcrumbs :items="[['label' => 'Dashboard']]" />
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Painel Operacional</h1>
        <p class="text-xs text-slate-500 mt-0.5">
            Visão consolidada da unidade: <strong class="text-slate-700 font-semibold">{{ $activeBranch?->name ?? 'Grupo Geral' }}</strong>
            @if($activeBranch?->is_headquarters)
                <x-badge variant="info" size="sm" class="ml-1.5">Matriz</x-badge>
            @endif
        </p>
    </div>

    <!-- Indicador de Último Acesso -->
    <div class="flex items-center gap-2 self-start sm:self-auto">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200/80 text-xs text-slate-600 shadow-2xs">
            <x-icon name="clock" class="w-3.5 h-3.5 text-slate-400" />
            <span>Último login: <strong class="text-slate-800 font-medium">{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d/m/Y H:i') : 'Primeiro acesso' }}</strong></span>
        </div>
    </div>
</div>

<!-- Grid de Indicadores KPI -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-kpi-card
        title="Filiais Autorizadas"
        :value="$accessibleBranches->count()"
        description="Lojas com acesso concedido"
        icon="store"
        iconColor="blue"
    />

    <x-kpi-card
        title="Depósitos de Estoque"
        :value="$warehousesCount"
        description="Locais ativos na filial selecionada"
        icon="boxes"
        iconColor="emerald"
    />

    <x-kpi-card
        title="PDVs em Operação"
        :value="$activePosCount"
        description="Frentes de caixa liberadas"
        icon="scan-barcode"
        iconColor="indigo"
    />

    <x-kpi-card
        title="Equipe da Unidade"
        :value="$usersCount"
        description="Colaboradores vinculados"
        icon="users"
        iconColor="amber"
    />
</div>

<!-- Seção Operacional Principal -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Situação e Infraestrutura da Unidade (2 colunas) -->
    <div class="lg:col-span-2 space-y-6">

        <x-card title="Situação da Unidade Operacional" subtitle="Dados fiscais e estrutura física de armazenagem">
            @if($activeBranch)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-xs pb-4 border-b border-slate-100">
                    <div>
                        <span class="text-slate-400 uppercase tracking-wider block text-[10px] font-semibold mb-0.5">Empresa / Razão Social</span>
                        <span class="font-medium text-slate-800 text-sm">{{ $activeBranch->company?->name ?? 'N/D' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 uppercase tracking-wider block text-[10px] font-semibold mb-0.5">CNPJ da Filial</span>
                        <span class="font-mono font-medium text-slate-800">{{ $activeBranch->cnpj ?? 'Não informado' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 uppercase tracking-wider block text-[10px] font-semibold mb-0.5">Endereço da Loja</span>
                        <span class="text-slate-700 leading-relaxed">{{ $activeBranch->formatted_address ?: 'Endereço não cadastrado' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 uppercase tracking-wider block text-[10px] font-semibold mb-0.5">Canais de Contato</span>
                        <span class="text-slate-700">{{ $activeBranch->phone ?? 'Sem telefone' }} &bull; {{ $activeBranch->email ?? 'Sem e-mail' }}</span>
                    </div>
                </div>

                <div class="pt-4">
                    <span class="text-slate-400 uppercase tracking-wider block text-[10px] font-bold mb-3">Estrutura de Armazenamento Chave</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3.5 rounded-xl border border-slate-200/70 bg-slate-50/50 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100/70 text-blue-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <x-icon name="check-circle" class="w-4 h-4" />
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block">Recebimento de Mercadorias</span>
                                <span class="font-medium text-slate-800 text-xs mt-0.5 block">
                                    {{ $activeBranch->defaultWarehouse?->name ?? 'Nenhum definido' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl border border-slate-200/70 bg-slate-50/50 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100/70 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <x-icon name="store" class="w-4 h-4" />
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block">Área de Vendas (Gôndolas)</span>
                                <span class="font-medium text-slate-800 text-xs mt-0.5 block">
                                    {{ $activeBranch->salesFloorWarehouse?->name ?? 'Nenhuma definida' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <x-empty-state
                    icon="store"
                    title="Nenhuma filial selecionada"
                    description="Selecione uma filial no topo para carregar a situação operacional."
                />
            @endif
        </x-card>

        <!-- Trilha de Auditoria Recente -->
        <x-card title="Trilha de Auditoria da Filial" subtitle="Últimas ações e acessos registrados em tempo real">
            <x-slot:action>
                <x-button :href="route('audit.index')" variant="ghost" size="sm" iconRight="arrow-right">
                    Ver todos
                </x-button>
            </x-slot:action>

            <div class="-mx-6 -my-6 overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/80 border-b border-slate-100 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-3">Horário</th>
                            <th class="px-6 py-3">Usuário</th>
                            <th class="px-6 py-3">Ação</th>
                            <th class="px-6 py-3">Descrição</th>
                            <th class="px-6 py-3 text-right">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentAudits as $log)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                    {{ $log->created_at->format('d/m H:i:s') }}
                                </td>
                                <td class="px-6 py-3 font-medium text-slate-800 whitespace-nowrap">
                                    {{ $log->user?->name ?? 'Sistema' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @php
                                        $v = match($log->action) {
                                            'login' => 'success',
                                            'logout' => 'neutral',
                                            'login_failed' => 'danger',
                                            'switch_branch' => 'info',
                                            'create' => 'info',
                                            'update' => 'warning',
                                            'delete' => 'danger',
                                            default => 'neutral',
                                        };
                                    @endphp
                                    <x-badge :variant="$v" size="sm">
                                        {{ $log->action }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-3 text-slate-600 max-w-xs truncate">
                                    {{ $log->description ?? '-' }}
                                </td>
                                <td class="px-6 py-3 text-right font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                    Nenhum log registrado recentemente nesta unidade.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

    </div>

    <!-- Navegação e Ações Rápidas (1 coluna) -->
    <div class="space-y-6">

        <x-card title="Atalhos Rápidos" subtitle="Navegação ágil para módulos operacionais">
            <div class="space-y-2">
                <a href="{{ route('branches.index') }}"
                   class="flex items-center justify-between p-3 rounded-xl border border-slate-200/70 hover:border-blue-200 hover:bg-blue-50/40 text-slate-700 hover:text-blue-700 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <x-icon name="store" class="w-4 h-4" />
                        </div>
                        <div class="text-xs">
                            <div class="font-semibold">Gerenciar Filiais</div>
                            <div class="text-[11px] text-slate-400 group-hover:text-blue-600/70">Unidades e lojas da rede</div>
                        </div>
                    </div>
                    <x-icon name="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-blue-600 transition-colors" />
                </a>

                <a href="{{ route('warehouses.index') }}"
                   class="flex items-center justify-between p-3 rounded-xl border border-slate-200/70 hover:border-emerald-200 hover:bg-emerald-50/40 text-slate-700 hover:text-emerald-700 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <x-icon name="boxes" class="w-4 h-4" />
                        </div>
                        <div class="text-xs">
                            <div class="font-semibold">Depósitos de Estoque</div>
                            <div class="text-[11px] text-slate-400 group-hover:text-emerald-600/70">Locais da filial ativa</div>
                        </div>
                    </div>
                    <x-icon name="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-emerald-600 transition-colors" />
                </a>

                <a href="{{ route('pos.index') }}"
                   class="flex items-center justify-between p-3 rounded-xl border border-slate-200/70 hover:border-indigo-200 hover:bg-indigo-50/40 text-slate-700 hover:text-indigo-700 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <x-icon name="scan-barcode" class="w-4 h-4" />
                        </div>
                        <div class="text-xs">
                            <div class="font-semibold">PDVs & Caixas</div>
                            <div class="text-[11px] text-slate-400 group-hover:text-indigo-600/70">Frentes de loja ativas</div>
                        </div>
                    </div>
                    <x-icon name="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-indigo-600 transition-colors" />
                </a>

                <a href="{{ route('users.index') }}"
                   class="flex items-center justify-between p-3 rounded-xl border border-slate-200/70 hover:border-amber-200 hover:bg-amber-50/40 text-slate-700 hover:text-amber-700 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-colors">
                            <x-icon name="users" class="w-4 h-4" />
                        </div>
                        <div class="text-xs">
                            <div class="font-semibold">Usuários & Permissões</div>
                            <div class="text-[11px] text-slate-400 group-hover:text-amber-600/70">Acessos por filial</div>
                        </div>
                    </div>
                    <x-icon name="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-amber-600 transition-colors" />
                </a>
            </div>
        </x-card>

        <!-- Informações de Apoio ao Operador -->
        <x-card title="Segurança & Conformidade" subtitle="Parâmetros ativos da sessão atual">
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-500">Perfil Ativo:</span>
                    <span class="font-semibold text-slate-800">{{ $currentRole?->label ?? 'Superadmin' }}</span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-500">Ambiente de Operação:</span>
                    <x-badge variant="success" size="sm" :dot="true">Local Seguro</x-badge>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Auditoria Automática:</span>
                    <span class="text-emerald-600 font-medium flex items-center gap-1">
                        <x-icon name="shield-check" class="w-3.5 h-3.5" />
                        Ativa (Append-only)
                    </span>
                </div>
            </div>
        </x-card>

    </div>

</div>
@endsection
