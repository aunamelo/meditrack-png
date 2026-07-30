@props(['panel'])

@php
    $items = $panel['items'] ?? [];
@endphp

<div class="medcare-panel flex h-full flex-col overflow-hidden">
    <div class="border-b border-line/80 px-5 py-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-brand-200">Priorities</p>
                <h3 class="mt-1 font-display text-base font-bold text-ink">{{ $panel['title'] }}</h3>
                <p class="mt-0.5 text-xs text-muted">{{ $panel['subtitle'] }}</p>
            </div>
            @if(! empty($panel['more_url']))
                <a href="{{ $panel['more_url'] }}" class="shrink-0 text-xs font-semibold text-brand-600 hover:underline">
                    {{ ($panel['count'] ?? 0) > 0 ? $panel['count'].' at risk' : 'View' }}
                </a>
            @endif
        </div>
    </div>

    @if(count($items))
        <ol class="flex-1 divide-y divide-line/70">
            @foreach($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="flex items-start gap-3 px-5 py-3.5 transition hover:bg-canvas/80">
                        <span @class([
                            'mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs font-bold',
                            'bg-rose-100 text-rose-700' => ($item['tone'] ?? '') === 'red',
                            'bg-amber-100 text-amber-800' => ($item['tone'] ?? '') === 'amber',
                            'bg-brand-50 text-brand-700' => ! in_array($item['tone'] ?? '', ['red', 'amber'], true),
                        ])>{{ $loop->iteration }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-semibold text-ink">{{ $item['label'] }}</p>
                                <span @class([
                                    'shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                    'bg-rose-100 text-rose-800' => ($item['tone'] ?? '') === 'red',
                                    'bg-amber-100 text-amber-800' => ($item['tone'] ?? '') === 'amber',
                                ])>{{ $item['status_label'] }}</span>
                            </div>
                            <p class="mt-0.5 truncate text-xs text-muted">{{ $item['detail'] }}</p>
                        </div>
                    </a>
                </li>
            @endforeach
        </ol>
    @else
        <div class="flex flex-1 flex-col items-center justify-center px-5 py-10 text-center">
            <p class="text-sm font-medium text-ink">No medicines at risk</p>
            <p class="mt-1 text-xs text-muted">Stock cover looks adequate right now.</p>
        </div>
    @endif
</div>
