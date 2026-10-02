@extends('layouts.guest')

@section('title', 'Acesso ao Sistema')

@section('content')
<div class="space-y-6">

    <!-- Card Principal de Login -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-8 shadow-sm">

        <!-- Logo & Header -->
        <div class="text-center mb-7">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-600 text-white shadow-xs mb-3">
                <x-icon name="shopping-cart" class="w-6 h-6" />
            </div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">MarketCore ERP</h2>
            <p class="text-xs text-slate-500 mt-1">Gestão Integrada para Redes de Supermercados</p>
        </div>

        @if(session('info'))
            <div class="mb-4">
                <x-alert variant="info">
                    {{ session('info') }}
                </x-alert>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4">
                <x-alert variant="error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </x-alert>
            </div>
        @endif

        <!-- Formulário -->
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <x-input
                label="E-mail de Acesso"
                name="email"
                id="email"
                type="email"
                icon="mail"
                value="{{ old('email', 'superadmin@marketcore.com') }}"
                placeholder="nome@mercado.com.br"
                required
            />

            <x-input
                label="Senha"
                name="password"
                id="password"
                type="password"
                icon="lock"
                value="senha123"
                placeholder="••••••••"
                required
            />

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" id="remember" value="1"
                           class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20">
                    <span class="text-xs text-slate-600 select-none">Lembrar acesso</span>
                </label>

                <div class="flex items-center gap-1 text-[11px] text-emerald-600 font-medium">
                    <x-icon name="shield-check" class="w-3.5 h-3.5" />
                    <span>Conexão Segura</span>
                </div>
            </div>

            <div class="pt-2">
                <x-button type="submit" variant="primary" size="lg" icon="log-in" class="w-full">
                    Acessar Plataforma
                </x-button>
            </div>
        </form>

        <hr class="my-6 border-slate-100">

        <!-- Contas Rápidas de Avaliação -->
        <div>
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Perfis para Avaliação (Etapa 1)</span>
                <span class="text-[10px] text-slate-400 font-mono">senha123</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" class="quick-login text-left p-2 rounded-xl border border-slate-200/80 bg-slate-50/60 hover:bg-blue-50/60 hover:border-blue-200 transition-colors" data-email="superadmin@marketcore.com">
                    <div class="font-semibold text-slate-800">Superadmin</div>
                    <div class="text-[10px] text-slate-400">Acesso global</div>
                </button>

                <button type="button" class="quick-login text-left p-2 rounded-xl border border-slate-200/80 bg-slate-50/60 hover:bg-blue-50/60 hover:border-blue-200 transition-colors" data-email="admin@alvorada.com">
                    <div class="font-semibold text-slate-800">Admin Geral</div>
                    <div class="text-[10px] text-slate-400">Todas as filiais</div>
                </button>

                <button type="button" class="quick-login text-left p-2 rounded-xl border border-slate-200/80 bg-slate-50/60 hover:bg-blue-50/60 hover:border-blue-200 transition-colors" data-email="gerente.centro@alvorada.com">
                    <div class="font-semibold text-slate-800">Gerente Centro</div>
                    <div class="text-[10px] text-slate-400">Apenas Loja 1</div>
                </button>

                <button type="button" class="quick-login text-left p-2 rounded-xl border border-slate-200/80 bg-slate-50/60 hover:bg-blue-50/60 hover:border-blue-200 transition-colors" data-email="multirole@alvorada.com">
                    <div class="font-semibold text-slate-800">Multirole</div>
                    <div class="text-[10px] text-slate-400">Gerente L1 / Op L2</div>
                </button>
            </div>
        </div>

    </div>

    <!-- Rodapé -->
    <div class="text-center text-xs text-slate-400">
        MarketCore SaaS ERP &bull; Alta Disponibilidade
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.quick-login').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('email').value = this.getAttribute('data-email');
            document.getElementById('password').value = 'senha123';
        });
    });
</script>
@endpush
