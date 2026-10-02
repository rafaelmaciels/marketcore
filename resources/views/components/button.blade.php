@props([
    'variant' => 'primary', // primary, secondary, danger, ghost, outline
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconRight' => null,
    'href' => null,
    'type' => 'button',
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-60 disabled:pointer-events-none cursor-pointer';

    $sizes = [
        'sm' => 'text-xs px-3 py-1.5 gap-1.5 h-8',
        'md' => 'text-sm px-4 py-2 gap-2 h-9.5',
        'lg' => 'text-base px-5 py-2.5 gap-2.5 h-11',
    ];

    $variants = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 shadow-[0_1px_2px_0_rgba(37,99,235,0.2)] focus:ring-blue-500',
        'secondary' => 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 shadow-[0_1px_2px_0_rgba(0,0,0,0.03)] focus:ring-slate-400',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 active:bg-rose-800 shadow-[0_1px_2px_0_rgba(225,29,72,0.2)] focus:ring-rose-500',
        'ghost' => 'text-slate-600 hover:text-slate-950 hover:bg-slate-100 active:bg-slate-200 focus:ring-slate-400',
        'outline' => 'bg-transparent border border-blue-600 text-blue-600 hover:bg-blue-50 focus:ring-blue-500',
    ];

    $classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-icon :name="$icon" class="{{ $size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4' }}" />
        @endif
        <span>{{ $slot }}</span>
        @if($iconRight)
            <x-icon :name="$iconRight" class="{{ $size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4' }}" />
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-icon :name="$icon" class="{{ $size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4' }}" />
        @endif
        <span>{{ $slot }}</span>
        @if($iconRight)
            <x-icon :name="$iconRight" class="{{ $size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4' }}" />
        @endif
    </button>
@endif
