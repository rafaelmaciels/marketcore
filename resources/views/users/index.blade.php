@extends('layouts.app')

@section('title', 'Usuários e Permissões')

@section('content')
<div class="space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => 'Usuários']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Usuários & Controle de Acesso</h1>
            <p class="text-xs text-slate-500 mt-0.5">Gestão de colaboradores, perfis e permissões granulares por filial</p>
        </div>

        @can('create', App\Models\User::class)
            <x-button :href="route('users.create')" variant="primary" icon="user-plus">
                Novo Usuário
            </x-button>
        @endcan
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-3 shadow-2xs">
        <form action="{{ route('users.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <x-input
                    name="search"
                    placeholder="Buscar por nome, e-mail ou telefone..."
                    icon="search"
                    value="{{ request('search') }}"
                />
            </div>
            <div>
                <x-select name="role_id">
                    <option value="">Todos os Perfis</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>
                            {{ $r->label }}
                        </option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-select name="status">
                    <option value="">Todos os Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                </x-select>
            </div>
            <div class="flex items-center gap-2">
                <x-button type="submit" variant="secondary" size="md">
                    Filtrar
                </x-button>
                @if(request()->hasAny(['search', 'role_id', 'status']))
                    <x-button :href="route('users.index')" variant="ghost" size="md">
                        Limpar
                    </x-button>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabela de Usuários -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5">Colaborador</th>
                        <th class="px-5 py-3.5">E-mail</th>
                        <th class="px-5 py-3.5">Perfil Padrão</th>
                        <th class="px-5 py-3.5">Escopo de Filiais</th>
                        <th class="px-5 py-3.5">Último Acesso</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 font-bold text-xs flex items-center justify-center border border-blue-200/60 flex-shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <div class="font-semibold text-slate-900 text-sm flex items-center gap-1.5">
                                            <span>{{ $u->name }}</span>
                                            @if($u->is_superadmin)
                                                <x-badge variant="danger" size="sm">Superadmin</x-badge>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $u->company?->name ?? 'Acesso Global' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-600 whitespace-nowrap">
                                {{ $u->email }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-md bg-slate-100 text-slate-700 text-xs font-medium border border-slate-200/60">
                                    {{ $u->role?->label ?? 'Sem perfil padrão' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($u->is_superadmin || $u->all_branches)
                                    <x-badge variant="info" size="sm">
                                        <x-icon name="globe" class="w-3 h-3 mr-1" />
                                        Todas as Filiais
                                    </x-badge>
                                @else
                                    <div class="space-y-0.5">
                                        <span class="text-xs font-medium text-slate-700 block">
                                            {{ $u->branches->count() }} filial(is) atribuída(s)
                                        </span>
                                        <div class="text-[11px] text-slate-400 truncate max-w-xs">
                                            {{ $u->branches->pluck('name')->join(', ') }}
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($u->last_login_at)
                                    <div class="font-medium text-slate-700 text-xs">{{ $u->last_login_at->format('d/m/Y H:i') }}</div>
                                    <div class="font-mono text-[10px] text-slate-400">IP: {{ $u->last_login_ip }}</div>
                                @else
                                    <span class="text-slate-400 text-xs">Nunca acessou</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @php
                                    $v = match($u->status) {
                                        'active' => 'success',
                                        'blocked' => 'danger',
                                        default => 'neutral',
                                    };
                                @endphp
                                <x-badge :variant="$v" size="sm" :dot="true">
                                    {{ $u->status === 'active' ? 'Ativo' : ($u->status === 'blocked' ? 'Bloqueado' : 'Inativo') }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                @can('update', $u)
                                    <x-button :href="route('users.edit', $u)" variant="ghost" size="sm" title="Editar Usuário">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </x-button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state
                                    icon="users"
                                    title="Nenhum usuário encontrado"
                                    description="Não encontramos colaboradores com os filtros aplicados."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
