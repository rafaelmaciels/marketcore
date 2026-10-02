@props([
    'title',
    'value',
    'description' => null,
    'icon' => null,
    'iconColor' => 'blue',
    'change' => null,
    'changeType' => 'neutral', // 'positive', 'negative', 'neutral'
])

@php
    $colorVariants = [
        'blue' => 'bg-blue-50 text-blue-600 border-blue-100/60',
        'emerald' => 'bg-emerald-50 text-emerald-600 border-emerald-100/60',
        'indigo' => 'bg-indigo-50 text-indigo-600 border-indigo-100/60',
        'amber' => 'bg-amber-50 text-amber-600 border-amber-100/60',
        'rose' => 'bg-rose-50 text-rose-600 border-rose-100/60',
        'slate' => 'bg-slate-50 text-slate-600 border-slate-200/60',
    ];
    $iconStyle = $colorVariants[$iconColor] ?? $colorVariants['blue'];
@endphp

<div class="bg-white rounded-2xl border border-slate-200/80 p-5.5 shadow-[0_1px_3px_0_rgba(15,23,42,0.03)] hover:shadow-md hover:border-slate-300/80 transition-all duration-200 flex flex-col justify-between">
    <div class="flex items-center justify-between gap-3 mb-3">
        <span class="text-xs font-medium text-slate-500 tracking-wider uppercase">{{ $title }}</span>
        @if($icon)
            <div class="w-9 h-9 rounded-xl border flex items-center justify-center {{ $iconStyle }}">
                <x-icon :name="$icon" class="w-4.5 h-4.5" />
            </div>
        @endif
    </div>

    <div>
        <div class="text-2xl font-bold tracking-tight text-slate-900 mb-1">
            {{ $value }}
        </div>
        
        @if($description || $change)
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                @if($change)
                    <span class="font-semibold {{ $changeType === 'positive' ? 'text-emerald-600' : ($changeType === 'negative' ? 'text-rose-600' : 'text-slate-600') }}">
                        {{ $change }}
                    </span>
                @endif
                @if($description)
                    <span class="text-slate-500 truncate">{{ $description }}</span>
                @endif
            </div>
        @endif
    </div>
</div>
