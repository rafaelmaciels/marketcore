<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') | MarketCore ERP</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets (Tailwind CSS, Alpine.js, Lucide Icons) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased font-sans flex overflow-hidden"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('mc_sidebar_collapsed') === 'true',
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('mc_sidebar_collapsed', this.sidebarCollapsed);
              this.$nextTick(() => { window.renderLucideIcons && window.renderLucideIcons(); });
          }
      }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-xs lg:hidden"
         style="display: none;">
    </div>

    <!-- Barra Lateral (Sidebar) -->
    <aside :class="{
                'translate-x-0': sidebarOpen,
                '-translate-x-full': !sidebarOpen,
                'lg:w-64': !sidebarCollapsed,
                'lg:w-20': sidebarCollapsed
           }"
           class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-slate-200/80 transition-all duration-300 ease-in-out lg:static lg:translate-x-0 h-full flex-shrink-0">

        <!-- Logotipo e Identidade da Marca -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-100 flex-shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden group">
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-xs group-hover:bg-blue-700 transition-colors flex-shrink-0">
                    <x-icon name="shopping-cart" class="w-5 h-5" />
                </div>
                <div x-show="!sidebarCollapsed" x-transition.opacity class="flex items-center gap-1.5 whitespace-nowrap">
                    <span class="font-bold text-slate-900 tracking-tight text-base">MarketCore</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200/50">ERP</span>
                </div>
            </a>

            <!-- Botão Fechar no Mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <!-- Menus de Navegação -->
        <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-6">

            <!-- Grupo: Principal -->
            <div>
                <div x-show="!sidebarCollapsed" class="px-2.5 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Visão Geral
                </div>
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('dashboard') ? 'bg-blue-50/80 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                       :title="sidebarCollapsed ? 'Dashboard' : ''">
                        <x-icon name="layout-dashboard" class="w-4.5 h-4.5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Grupo: Catálogo & Produtos -->
            <div>
                <div x-show="!sidebarCollapsed" class="px-2.5 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Catálogo de Produtos
                </div>
                <div class="space-y-1">
                    <a href="{{ route('products.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('products.*') ? 'bg-blue-50/80 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                       :title="sidebarCollapsed ? 'Produtos' : ''">
                        <x-icon name="package" class="w-4.5 h-4.5 flex-shrink-0 {{ request()->routeIs('products.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">Produtos</span>
                    </a>

                    <a href="{{ route('categories.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('categories.*') ? 'bg-blue-50/80 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                       :title="sidebarCollapsed ? 'Categorias' : ''">
                        <x-icon name="folder-tree" class="w-4.5 h-4.5 flex-shrink-0 {{ request()->routeIs('categories.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">Departamentos & Categorias</span>
                    </a>

                    <a href="{{ route('brands.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('brands.*') ? 'bg-blue-50/80 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                       :title="sidebarCollapsed ? 'Marcas' : ''">
                        <x-icon name="tag" class="w-4.5 h-4.5 flex-shrink-0 {{ request()->routeIs('brands.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">Marcas</span>
                    </a>
                </div>
            </div>

            <!-- Grupo: Estrutura & Lojas -->
            <div>
                <div x-show="!sidebarCollapsed" class="px-2.5 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Estrutura & Lojas
                </div>
                <div class="space-y-1">
                    <a href="{{ route('company.show') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('company.*') ? 'bg-blue-50/80 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                       :title="sidebarCollapsed ? 'Dados da Empresa' : ''">
                        <x-icon name="building" class="w-4.5 h-4.5 flex-shrink-0 {{ request()->routeIs('company.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">Dados da Empresa</span>
                    </a>

                    <a href="{{ route('branches.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('branches.*') ? 'bg-blue-50/80 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                       :title="sidebarCollapsed ? 'Filiais & Unidades' : ''">
                        <x-icon name="store" class="w-4.5 h-4.5 flex-shrink-0 {{ request()->routeIs('branches.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">Filiais & Unidades</span>
                    </a>

                    <a href="{{ route('warehouses.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('warehouses.*') ? 'bg-blue-50/80 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                       :title="sidebarCollapsed ? 'Depósitos de Estoque' : ''">
                        <x-icon name="boxes" class="w-4.5 h-4.5 flex-shrink-0 {{ request()->routeIs('warehouses.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">Depósitos da Loja</span>
                    </a>

                    <a href="{{ route('pos.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('pos.*') ? 'bg-blue-50/80 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                       :title="sidebarCollapsed ? 'PDVs & Caixas' : ''">
                        <x-icon name="scan-barcode" class="w-4.5 h-4.5 flex-shrink-0 {{ request()->routeIs('pos.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">Frentes de Caixa (PDV)</span>
                    </a>
                </div>
            </div>

            <!-- Grupo: Governança & Acessos -->
            <div>
                <div x-show="!sidebarCollapsed" class="px-2.5 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Acessos & Auditoria
                </div>
                <div class="space-y-1">
                    <a href="{{ route('users.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('users.*') ? 'bg-blue-50/80 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                       :title="sidebarCollapsed ? 'Usuários & Permissões' : ''">
                        <x-icon name="users" class="w-4.5 h-4.5 flex-shrink-0 {{ request()->routeIs('users.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">Usuários & Filiais</span>
                    </a>

                    <a href="{{ route('audit.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all group {{ request()->routeIs('audit.*') ? 'bg-blue-50/80 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                       :title="sidebarCollapsed ? 'Logs de Auditoria' : ''">
                        <x-icon name="shield-check" class="w-4.5 h-4.5 flex-shrink-0 {{ request()->routeIs('audit.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" />
                        <span x-show="!sidebarCollapsed" class="truncate">Logs de Auditoria</span>
                    </a>
                </div>
            </div>

        </nav>

        <!-- Botão Toggle Collapse (Desktop) & Rodapé do Usuário -->
        <div class="border-t border-slate-100 p-3 bg-slate-50/50 flex flex-col gap-2">
            <!-- Botão Recolher/Expandir Sidebar em Desktop -->
            <button @click="toggleSidebar()"
                    class="hidden lg:flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors w-full">
                <x-icon name="panel-left-close" class="w-4 h-4 transition-transform duration-300" ::class="sidebarCollapsed ? 'rotate-180' : ''" />
                <span x-show="!sidebarCollapsed">Recolher menu</span>
            </button>

            <!-- Card Perfil Resumido -->
            <div class="flex items-center gap-3 px-2 py-1.5 rounded-xl bg-white border border-slate-200/60 shadow-2xs">
                <span class="w-8 h-8 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </span>
                <div x-show="!sidebarCollapsed" class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-slate-800 truncate leading-tight">{{ auth()->user()->name }}</div>
                    <div class="text-[11px] text-slate-400 truncate">
                        {{ $activeRole?->label ?? (auth()->user()->isSuperAdmin() ? 'Superadmin' : 'Usuário') }}
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" x-show="!sidebarCollapsed" class="m-0">
                    @csrf
                    <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Encerrar Sessão">
                        <x-icon name="log-out" class="w-4 h-4" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

        <!-- Barra Superior (Header / Topbar) -->
        <header class="h-16 bg-white border-b border-slate-200/80 px-4 lg:px-7 flex items-center justify-between gap-4 flex-shrink-0 z-30">

            <div class="flex items-center gap-3">
                <!-- Botão Menu Mobile -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                    <x-icon name="menu" class="w-5 h-5" />
                </button>

                <!-- Seletor de Filial Ativa -->
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                            type="button"
                            class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/80 text-slate-800 text-xs font-medium transition-all shadow-2xs focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <div class="text-left">
                            <span class="block text-[10px] text-slate-400 font-semibold uppercase tracking-wider leading-none mb-0.5">Filial Ativa</span>
                            <span class="font-semibold text-slate-800 block text-xs leading-tight">
                                {{ $activeBranch ? $activeBranch->name : 'Selecione uma filial' }}
                            </span>
                        </div>
                        <x-icon name="chevrons-up-down" class="w-3.5 h-3.5 text-slate-400 ml-1" />
                    </button>

                    <!-- Dropdown Filiais -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         style="display: none;"
                         class="absolute left-0 mt-2 w-80 rounded-2xl bg-white shadow-xl border border-slate-200/90 py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Unidades Autorizadas ({{ $accessibleBranches->count() }})</span>
                            <x-icon name="store" class="w-3.5 h-3.5 text-slate-400" />
                        </div>
                        <div class="max-h-64 overflow-y-auto py-1">
                            @forelse($accessibleBranches as $b)
                                <form action="{{ route('branch.switch') }}" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $b->id }}">
                                    <button type="submit"
                                            class="w-full text-left px-4 py-2.5 text-xs flex items-center justify-between transition-colors {{ $activeBranch && $activeBranch->id === $b->id ? 'bg-blue-50/80 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $b->name }}</div>
                                            <div class="text-[11px] text-slate-400 mt-0.5">
                                                Cód: {{ $b->code ?? 'N/D' }} &bull; {{ $b->city ?? '' }}/{{ $b->state ?? '' }}
                                            </div>
                                        </div>
                                        @if($activeBranch && $activeBranch->id === $b->id)
                                            <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
                                                <x-icon name="check" class="w-3 h-3 stroke-[2.5]" />
                                            </span>
                                        @endif
                                    </button>
                                </form>
                            @empty
                                <div class="px-4 py-3 text-xs text-slate-400 text-center">Nenhuma filial vinculada</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Área Direita do Cabeçalho -->
            <div class="flex items-center gap-3">
                <!-- Badge Perfil na Filial Ativa -->
                <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-medium border border-slate-200/60">
                    <x-icon name="shield" class="w-3.5 h-3.5 text-blue-600" />
                    <span>{{ $activeRole?->label ?? (auth()->user()->isSuperAdmin() ? 'Superadmin Global' : 'Colaborador') }}</span>
                </div>

                <!-- Notificações (SaaS placeholder refinado) -->
                <div class="relative" x-data="{ open: false }">
                    <button class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors relative"
                            title="Notificações">
                        <x-icon name="bell" class="w-4.5 h-4.5" />
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-blue-600"></span>
                    </button>
                </div>

                <!-- Menu do Perfil do Usuário -->
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                            class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none">
                        <span class="w-8 h-8 rounded-xl bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </span>
                        <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         style="display: none;"
                         class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-slate-200/90 py-1.5 z-50">
                        <div class="px-4 py-2.5 border-b border-slate-100">
                            <div class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</div>
                            <div class="text-[10px] text-slate-400 font-mono mt-1">IP: {{ request()->ip() }}</div>
                        </div>

                        <div class="py-1">
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition-colors">
                                    <x-icon name="log-out" class="w-4 h-4" />
                                    <span>Encerrar Sessão</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </header>

        <!-- Área de Conteúdo com Rolagem Independente -->
        <main class="flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">

                <!-- Alertas Flash Globais -->
                @if(session('success'))
                    <x-alert variant="success" title="Operação Concluída">
                        {{ session('success') }}
                    </x-alert>
                @endif

                @if(session('error'))
                    <x-alert variant="error" title="Atenção">
                        {{ session('error') }}
                    </x-alert>
                @endif

                @if($errors->any())
                    <x-alert variant="error" title="Revise os seguintes erros:">
                        <ul class="list-disc pl-4 space-y-0.5 mt-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

                @yield('content')
            </div>
        </main>

        <!-- Rodapé do ERP -->
        <footer class="h-10 bg-white border-t border-slate-200/80 px-6 flex items-center justify-between text-xs text-slate-400 flex-shrink-0">
            <div>
                <span class="font-semibold text-slate-600">MarketCore ERP</span> &bull; Gestão de Supermercados
            </div>
            <div class="flex items-center gap-2">
                <span>Unidade: <strong class="text-slate-700">{{ $activeBranch?->name ?? 'Geral' }}</strong></span>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <span>v1.0.0</span>
            </div>
        </footer>

    </div>

    @stack('scripts')
</body>
</html>
