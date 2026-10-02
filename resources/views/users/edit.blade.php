@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <x-breadcrumbs :items="[['label' => 'Usuários', 'url' => route('users.index')], ['label' => $user->name, 'url' => route('users.show', $user)], ['label' => 'Editar']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Editar Colaborador: {{ $user->name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Atualização de perfis, dados de acesso e matriz de filiais autorizadas</p>
        </div>

        <x-button :href="route('users.index')" variant="secondary" icon="arrow-left">
            Voltar
        </x-button>
    </div>

    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">

            <!-- Card Dados Pessoais & Credenciais -->
            <x-card title="Credenciais & Perfil Geral" subtitle="Dados de autenticação corporativa">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <x-input label="Nome Completo" name="name" :value="$user->name" required />
                    </div>

                    <div>
                        <x-input label="E-mail de Acesso" name="email" type="email" :value="$user->email" required />
                    </div>

                    <div>
                        <x-input label="Alterar Senha" name="password" type="password" placeholder="Preencha apenas se for alterar" />
                    </div>

                    <div>
                        <x-input label="Telefone / Ramal" name="phone" :value="$user->phone" />
                    </div>

                    <div>
                        <x-select label="Status da Conta" name="status" required>
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Ativo</option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inativo</option>
                            <option value="blocked" {{ old('status', $user->status) === 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                        </x-select>
                    </div>

                    <div class="sm:col-span-2">
                        <x-select label="Perfil Padrão Global" name="role_id">
                            <option value="">Sem perfil global específico (definir por filial)</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->label }} - {{ $role->description }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3 pt-2">
                        <label class="flex items-center gap-2.5 p-3.5 rounded-xl border border-blue-200/80 bg-blue-50/40 cursor-pointer">
                            <input type="checkbox" name="all_branches" id="all_branches" value="1" {{ old('all_branches', $user->all_branches) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded-md border-blue-300 text-blue-600 focus:ring-blue-500/20">
                            <div>
                                <span class="text-xs font-semibold text-blue-900 block">Conceder acesso irrestrito a TODAS as filiais</span>
                                <span class="text-[11px] text-blue-700/80">O colaborador poderá alternar entre todas as lojas ativas da empresa.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </x-card>

            <!-- Card Matriz de Filiais e Papéis por Filial -->
            <x-card title="Matriz de Acesso e Perfis por Filial" subtitle="Defina filiais autorizadas, papel específico em cada unidade e filial preferencial">
                <div class="-mx-6 -my-6 overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50/80 border-b border-slate-100 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-6 py-3 w-16 text-center">Acesso</th>
                                <th class="px-6 py-3">Filial / Unidade</th>
                                <th class="px-6 py-3">Perfil Específico nesta Unidade</th>
                                <th class="px-6 py-3 text-center w-28">Filial Padrão</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($branches as $index => $branch)
                                @php
                                    $hasBranch = isset($userBranchesMap[$branch->id]);
                                    $pivotRole = $hasBranch ? $userBranchesMap[$branch->id]->pivot->role_id : null;
                                    $isDefault = $hasBranch ? $userBranchesMap[$branch->id]->pivot->is_default : false;
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-3 text-center">
                                        <input type="checkbox" name="branches[{{ $index }}][branch_id]" value="{{ $branch->id }}"
                                               class="branch-check w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20"
                                               id="b_check_{{ $branch->id }}" {{ $hasBranch ? 'checked' : '' }}>
                                    </td>
                                    <td class="px-6 py-3">
                                        <label for="b_check_{{ $branch->id }}" class="cursor-pointer">
                                            <span class="font-semibold text-slate-900 text-xs block">{{ $branch->name }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $branch->code ? 'Cód: ' . $branch->code . ' • ' : '' }}{{ $branch->city }}/{{ $branch->state }}</span>
                                        </label>
                                    </td>
                                    <td class="px-6 py-3">
                                        <select name="branches[{{ $index }}][role_id]"
                                                class="w-full max-w-xs h-9 text-xs bg-white rounded-xl border border-slate-200/90 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500/15 focus:border-blue-500">
                                            <option value="">Usar perfil padrão global</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ $pivotRole == $role->id ? 'selected' : '' }}>{{ $role->label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <input type="radio" name="default_branch_radio" value="{{ $index }}"
                                               class="branch-radio w-4 h-4 border-slate-300 text-blue-600 focus:ring-blue-500/20" {{ $isDefault ? 'checked' : '' }}>
                                        <input type="hidden" name="branches[{{ $index }}][is_default]" value="{{ $isDefault ? '1' : '0' }}" class="default-hidden">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- Ações -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button :href="route('users.index')" variant="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="primary" icon="check">
                    Salvar Alterações
                </x-button>
            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.branch-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.default-hidden').forEach(input => input.value = '0');
            const row = this.closest('tr');
            row.querySelector('.default-hidden').value = '1';
            row.querySelector('.branch-check').checked = true;
        });
    });
</script>
@endpush
