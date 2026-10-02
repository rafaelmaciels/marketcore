@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'error' => null,
])

@php
    $selectId = $id ?? ($name ?? uniqid('select_'));
    $hasError = $error || ($name && $errors->has($name));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $selectId }}" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl">
        <select
            name="{{ $name }}"
            id="{{ $selectId }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full h-10 text-sm bg-white rounded-xl border px-3.5 pr-8 transition-all duration-150 text-slate-800 focus:outline-none focus:ring-2 disabled:bg-slate-50 disabled:text-slate-400 appearance-none ' .
                ($hasError
                    ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/20'
                    : 'border-slate-200/90 hover:border-slate-300 focus:border-blue-500 focus:ring-blue-500/15 shadow-[0_1px_2px_0_rgba(0,0,0,0.02)]')
            ]) }}
        >
            {{ $slot }}
        </select>
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
            <x-icon name="chevron-down" class="w-4 h-4" />
        </div>
    </div>

    @if($errorMessage)
        <p class="mt-1 text-xs text-rose-600 flex items-center gap-1">
            <x-icon name="alert-circle" class="w-3.5 h-3.5 flex-shrink-0" />
            <span>{{ $errorMessage }}</span>
        </p>
    @elseif($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
