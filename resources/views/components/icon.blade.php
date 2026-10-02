@props([
    'name',
    'class' => 'w-5 h-5 text-current',
    'size' => null,
])

<i data-lucide="{{ $name }}" {{ $attributes->merge(['class' => $class]) }} @if($size) style="width: {{ $size }}px; height: {{ $size }}px;" @endif></i>
