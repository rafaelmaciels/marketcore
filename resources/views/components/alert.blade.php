@props([
    'variant' => 'info', // success, error, warning, info
    'title' => null,
    'dismissible' => true,
])

@php
    $configs = [
        'success' => [
            'wrap' => 'bg-emerald-50/90 border-emerald-200/60 text-emerald-900',
            'icon' => 'check-circle-2',
            'iconColor' => 'text-emerald-600',
        ],
        'error' => [
            'wrap' => 'bg-rose-50/90 border-rose-200/60 text-rose-900',
            'icon' => 'alert-triangle',
            'iconColor' => 'text-rose-600',
        ],
        'warning' => [
            'wrap' => 'bg-amber-50/90 border-amber-200/60 text-amber-900',
            'icon' => 'alert-circle',
            'iconColor' => 'text-amber-600',
        ],
        'info' => [
            'wrap' => 'bg-blue-50/90 border-blue-200/60 text-blue-900',
            'icon' => 'info',
            'iconColor' => 'text-blue-600',
        ],
    ];

    $cfg = $configs[$variant] ?? $configs['info'];
@endphp

<div x-data="{ open: true }" x-show="open" x-transition.opacity.duration.200ms
     class="rounded-xl border p-4 text-sm flex items-start gap-3 shadow-xs {{ $cfg['wrap'] }}">
    <div class="flex-shrink-0 mt-0.5 {{ $cfg['iconColor'] }}">
        <x-icon :name="$cfg['icon']" class="w-5 h-5" />
    </div>

    <div class="flex-1">
        @if($title)
            <h4 class="font-semibold text-sm mb-0.5 leading-tight">{{ $title }}</h4>
        @endif
        <div class="text-xs leading-relaxed opacity-90">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button type="button" @click="open = false" class="flex-shrink-0 p-1 rounded-lg hover:bg-black/5 text-current opacity-60 hover:opacity-100 transition-opacity">
            <x-icon name="x" class="w-4 h-4" />
        </button>
    @endif
</div>
