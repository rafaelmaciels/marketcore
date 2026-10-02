@extends('layouts.app')

@section('title', 'Registro de Auditoria #' . $auditLog->id)

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <x-breadcrumbs :items="[['label' => 'Auditoria', 'url' => route('audit.index')], ['label' => 'Log #' . $auditLog->id]]" />
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Registro de Auditoria #{{ $auditLog->id }}</h1>
                <x-badge variant="neutral" size="sm" class="uppercase font-mono">{{ $auditLog->action }}</x-badge>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Rastreabilidade completa de alteração e proveniência</p>
        </div>

        <x-button :href="route('audit.index')" variant="secondary" icon="arrow-left">
            Voltar
        </x-button>
    </div>

    <!-- Metadados em 2 Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <x-card title="Metadados do Evento" subtitle="Informações de contexto e autoria">
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">Data & Hora:</span>
                    <span class="font-mono font-medium text-slate-800">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">Usuário Responsável:</span>
                    <span class="font-medium text-slate-800">{{ $auditLog->user?->name ?? 'Sistema' }} ({{ $auditLog->user?->email ?? '-' }})</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">Filial Ativa no Momento:</span>
                    <span class="text-slate-700">{{ $auditLog->branch?->name ?? 'Global / Sem Filial' }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">Entidade Auditada:</span>
                    <span class="font-mono text-slate-700">{{ $auditLog->auditable_type ? class_basename($auditLog->auditable_type) . ' #' . $auditLog->auditable_id : 'N/A' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1">Descrição do Evento:</span>
                    <span class="text-slate-800 leading-relaxed block">{{ $auditLog->description ?? '-' }}</span>
                </div>
            </div>
        </x-card>

        <x-card title="Origem da Conexão" subtitle="Parâmetros de rede e cliente">
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-slate-400">Endereço IP:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $auditLog->ip_address ?? '127.0.0.1' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1.5">User-Agent / Navegador:</span>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 font-mono text-[11px] text-slate-600 break-all leading-relaxed">
                        {{ $auditLog->user_agent ?? 'Não registrado' }}
                    </div>
                </div>
            </div>
        </x-card>

    </div>

    <!-- Valores Anteriores x Valores Novos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <x-card title="Valores Anteriores (Old Values)" subtitle="Estado do registro antes da alteração">
            @if($auditLog->old_values && count($auditLog->old_values) > 0)
                <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl font-mono text-xs overflow-x-auto leading-relaxed"><code>{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
            @else
                <div class="text-xs text-slate-400 italic py-6 text-center">
                    Nenhum valor anterior registrado (Criação inicial ou ação sem estado prévio).
                </div>
            @endif
        </x-card>

        <x-card title="Valores Novos (New Values)" subtitle="Novo estado gravado no banco de dados">
            @if($auditLog->new_values && count($auditLog->new_values) > 0)
                <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl font-mono text-xs overflow-x-auto leading-relaxed"><code>{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
            @else
                <div class="text-xs text-slate-400 italic py-6 text-center">
                    Nenhum valor novo registrado.
                </div>
            @endif
        </x-card>

    </div>

</div>
@endsection
