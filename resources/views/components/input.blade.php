@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'icon' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'error' => null,
])

@php
    $inputId = $id ?? ($name ?? uniqid('input_'));
    $hasError = $error || ($name && $errors->has($name));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <x-icon :name="$icon" class="w-4 h-4" />
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full h-10 text-sm bg-white rounded-xl border transition-all duration-150 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 disabled:bg-slate-50 disabled:text-slate-400 ' .
                ($icon ? 'pl-9.5 pr-3.5 ' : 'px-3.5 ') .
                ($hasError
                    ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 text-rose-900 placeholder-rose-300'
                    : 'border-slate-200/90 hover:border-slate-300 focus:border-blue-500 focus:ring-blue-500/15 shadow-[0_1px_2px_0_rgba(0,0,0,0.02)]')
            ]) }}
        />
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
