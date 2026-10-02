@props([
    'icon' => 'inbox',
    'title' => 'Nenhum registro encontrado',
    'description' => 'Não há dados para exibir com os critérios atuais.',
    'action' => null,
])

<div class="py-12 px-4 text-center flex flex-col items-center justify-center">
    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
        <x-icon :name="$icon" class="w-6 h-6 stroke-[1.5]" />
    </div>

    <h4 class="text-sm font-semibold text-slate-800 mb-1">{{ $title }}</h4>
    <p class="text-xs text-slate-500 max-w-sm mb-4 leading-relaxed">{{ $description }}</p>

    @if($action)
        <div>
            {{ $action }}
        </div>
    @endif
</div>
