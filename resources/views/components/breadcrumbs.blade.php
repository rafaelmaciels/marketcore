@props([
    'items' => [], // array de ['label' => '...', 'url' => '...']
])

<nav class="flex items-center text-xs text-slate-500 mb-3" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1.5">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-900 transition-colors">
                <x-icon name="home" class="w-3.5 h-3.5" />
                <span>Início</span>
            </a>
        </li>

        @foreach($items as $item)
            <li>
                <div class="flex items-center">
                    <x-icon name="chevron-right" class="w-3 h-3 text-slate-400 mx-1 flex-shrink-0" />
                    @if(!empty($item['url']) && !$loop->last)
                        <a href="{{ $item['url'] }}" class="text-slate-500 hover:text-slate-900 transition-colors">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="font-medium text-slate-800">{{ $item['label'] }}</span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
