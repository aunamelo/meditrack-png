@props(['panel'])

@php
    $items = $panel['items'] ?? [];
@endphp

<div class="medcare-panel flex h-full flex-col overflow-hidden">
    <div class="border-b border-line/80 px-5 py-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-brand-200">Coverage</p>
                <h3 class="mt-1 font-display text-base font-bold text-ink">{{ $panel['title'] }}</h3>
                <p class="mt-0.5 text-xs text-muted">{{ $panel['subtitle'] }}</p>
            </div>
            @if(! empty($panel['more_url']))
                <a href="{{ $panel['more_url'] }}" class="shrink-0 text-xs font-semibold text-brand-600 hover:underline">View all</a>
            @endif
        </div>
    </div>

    @if(count($items))
        <ul class="flex-1 divide-y divide-line/70">
            @foreach($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="block px-5 py-3.5 transition hover:bg-canvas/80">
                        <div class="mb-1.5 flex items-center justify-between gap-2">
                            <p class="min-w-0 truncate text-sm font-semibold text-ink">{{ $item['label'] }}</p>
                            <span @class([
                                'shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                'bg-rose-100 text-rose-800' => in_array($item['tone'], ['red'], true),
                                'bg-amber-100 text-amber-800' => in_array($item['tone'], ['amber'], true),
                                'bg-emerald-100 text-emerald-800' => in_array($item['tone'], ['teal', 'green'], true) || ($item['tone'] ?? '') === 'green',
                                'bg-sky-100 text-sky-800' => ($item['tone'] ?? '') === 'blue',
                                'bg-canvas text-muted' => ! in_array($item['tone'] ?? '', ['red', 'amber', 'teal', 'green', 'blue'], true),
                            ])>{{ $item['status_label'] }}</span>
                        </div>
                        <div class="mb-1.5 h-2 overflow-hidden rounded-full bg-canvas dark:bg-zinc-800">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                @class([
                                    'bg-rose-500' => ($item['tone'] ?? '') === 'red',
                                    'bg-amber-500' => ($item['tone'] ?? '') === 'amber',
                                    'bg-brand-600' => in_array($item['tone'] ?? '', ['teal', 'green', 'blue'], true) || ! in_array($item['tone'] ?? '', ['red', 'amber'], true),
                                ])
                                style="width: {{ max(4, (int) $item['percent']) }}%"
                            ></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-muted">
                            <span>{{ $item['days_label'] }} cover</span>
                            <span>{{ $item['stock_label'] }}</span>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <div class="flex flex-1 items-center justify-center px-5 py-10 text-center text-sm text-muted">
            No stock coverage data yet.
        </div>
    @endif
</div>
