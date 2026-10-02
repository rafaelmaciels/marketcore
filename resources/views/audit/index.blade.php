@extends('layouts.app')

@section('title', 'Logs de Auditoria')

@section('content')
<div class="space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => 'Auditoria']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Trilha de Auditoria & Conformidade</h1>
            <p class="text-xs text-slate-500 mt-0.5">Registro imutável de acessos, trocas de filial e operações de dados</p>
        </div>

        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200/80 text-xs text-slate-600 shadow-2xs">
            <x-icon name="shield-check" class="w-4 h-4 text-emerald-600" />
            <span>Append-Only Ativo</span>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-3 shadow-2xs">
        <form action="{{ route('audit.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <x-input
                    name="search"
                    placeholder="Buscar por descrição, IP ou entidade..."
                    icon="search"
                    value="{{ request('search') }}"
                />
            </div>

            <div>
                <x-select name="action">
                    <option value="">Todas as Ações</option>
                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="login_failed" {{ request('action') === 'login_failed' ? 'selected' : '' }}>Tentativa Falha</option>
                    <option value="switch_branch" {{ request('action') === 'switch_branch' ? 'selected' : '' }}>Troca de Filial</option>
                    <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>Criação (Create)</option>
                    <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Atualização (Update)</option>
                    <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>Exclusão (Delete)</option>
                </x-select>
            </div>

            <div>
                <x-select name="branch_id">
                    <option value="">Todas as Filiais</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" variant="secondary" size="md">
                    Filtrar
                </x-button>
                @if(request()->hasAny(['search', 'action', 'branch_id']))
                    <x-button :href="route('audit.index')" variant="ghost" size="md">
                        Limpar
                    </x-button>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabela de Auditoria -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">Horário</th>
                        <th class="px-5 py-3.5">Usuário Responsável</th>
                        <th class="px-5 py-3.5">Filial / Contexto</th>
                        <th class="px-5 py-3.5">Ação</th>
                        <th class="px-5 py-3.5">Descrição</th>
                        <th class="px-5 py-3.5">Entidade / ID</th>
                        <th class="px-5 py-3.5">Endereço IP</th>
                        <th class="px-5 py-3.5 text-right">Detalhes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($log->user)
                                    <div class="font-semibold text-slate-900 text-xs">{{ $log->user->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $log->user->email }}</div>
                                @else
                                    <span class="text-slate-400 italic">Sistema</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                                {{ $log->branch?->name ?? 'Geral / Matriz' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
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
                            <td class="px-5 py-4 text-slate-700 max-w-xs truncate">
                                {{ $log->description ?? '-' }}
                            </td>
                            <td class="px-5 py-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                @if($log->auditable_type)
                                    {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <x-button :href="route('audit.show', $log)" variant="ghost" size="sm" title="Ver Detalhes">
                                    <x-icon name="eye" class="w-4 h-4" />
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state
                                    icon="shield-check"
                                    title="Nenhum log registrado"
                                    description="Não encontramos registros com os filtros informados."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
