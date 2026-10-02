@props([
    'headers' => [],
    'class' => '',
])

<div class="overflow-x-auto rounded-2xl border border-slate-200/80 bg-white shadow-[0_1px_3px_0_rgba(15,23,42,0.03)] {{ $class }}">
    <table class="w-full text-left text-sm text-slate-700">
        @if(count($headers) > 0)
            <thead class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-5 py-3.5 {{ is_array($header) ? ($header['class'] ?? '') : '' }}">
                            {{ is_array($header) ? $header['label'] : $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody class="divide-y divide-slate-100 font-normal">
            {{ $slot }}
        </tbody>
    </table>
</div>
