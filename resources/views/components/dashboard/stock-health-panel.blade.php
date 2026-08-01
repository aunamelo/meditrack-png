@props(['panel'])

@php
    $items = $panel['items'] ?? [];
@endphp

<div class="medcare-panel flex h-full flex-col overflow-hidden">
    <div class="border-b border-line px-3.5 py-2.5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $panel['title'] }}</h3>
                <p class="mt-0.5 text-[11px] text-muted">{{ $panel['subtitle'] }}</p>
            </div>
            @if(! empty($panel['more_url']))
                <a href="{{ $panel['more_url'] }}" class="shrink-0 text-xs font-semibold text-health-700 hover:underline dark:text-health-300">View all</a>
            @endif
        </div>
    </div>

    @if(count($items))
        <ul class="flex-1 divide-y divide-line">
            @foreach($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="block px-3.5 py-2.5 transition hover:bg-canvas">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <p class="min-w-0 truncate text-sm font-semibold text-ink">{{ $item['label'] }}</p>
                            <span @class([
                                'module-badge shrink-0',
                                'bg-rose-50 text-rose-800' => in_array($item['tone'], ['red'], true),
                                'bg-amber-50 text-amber-900' => in_array($item['tone'], ['amber'], true),
                                'bg-health-50 text-health-800' => in_array($item['tone'], ['teal', 'green'], true) || ($item['tone'] ?? '') === 'green',
                                'bg-brand-50 text-brand-800' => ($item['tone'] ?? '') === 'blue',
                                'bg-surface-muted text-muted' => ! in_array($item['tone'] ?? '', ['red', 'amber', 'teal', 'green', 'blue'], true),
                            ])>{{ $item['status_label'] }}</span>
                        </div>
                        <div class="mb-1 h-1.5 overflow-hidden rounded bg-canvas dark:bg-zinc-800">
                            <div
                                class="h-full rounded transition-all duration-300"
                                @class([
                                    'bg-rose-600' => ($item['tone'] ?? '') === 'red',
                                    'bg-amber-600' => ($item['tone'] ?? '') === 'amber',
                                    'bg-health-700' => in_array($item['tone'] ?? '', ['teal', 'green'], true),
                                    'bg-brand-700' => ($item['tone'] ?? '') === 'blue' || ! in_array($item['tone'] ?? '', ['red', 'amber', 'teal', 'green'], true),
                                ])
                                style="width: {{ max(4, (int) $item['percent']) }}%"
                            ></div>
                        </div>
                        <div class="flex justify-between text-[11px] tabular-nums text-muted">
                            <span>{{ $item['days_label'] }} cover</span>
                            <span>{{ $item['stock_label'] }}</span>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <div class="flex flex-1 items-center px-3.5 py-6 text-sm text-muted">
            No stock coverage data yet.
        </div>
    @endif
</div>
