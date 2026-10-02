@props([
    'variant' => 'neutral', // success, warning, danger, info, neutral, purple
    'size' => 'md', // sm, md
    'dot' => false,
])

@php
    $variants = [
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200/60',
        'danger'  => 'bg-rose-50 text-rose-700 border-rose-200/60',
        'info'    => 'bg-blue-50 text-blue-700 border-blue-200/60',
        'neutral' => 'bg-slate-100/80 text-slate-700 border-slate-200/70',
        'purple'  => 'bg-purple-50 text-purple-700 border-purple-200/60',
    ];

    $dotColors = [
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger'  => 'bg-rose-500',
        'info'    => 'bg-blue-500',
        'neutral' => 'bg-slate-400',
        'purple'  => 'bg-purple-500',
    ];

    $sizes = [
        'sm' => 'text-[11px] px-2 py-0.5 gap-1',
        'md' => 'text-xs px-2.5 py-1 gap-1.5',
    ];

    $badgeStyle = ($variants[$variant] ?? $variants['neutral']) . ' ' . ($sizes[$size] ?? $sizes['md']);
    $dotStyle = $dotColors[$variant] ?? $dotColors['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center font-medium rounded-full border ' . $badgeStyle]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotStyle }}"></span>
    @endif
    <span>{{ $slot }}</span>
</span>
