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
                <a href="{{ $panel['more_url'] }}" class="shrink-0 text-xs font-semibold text-health-700 hover:underline dark:text-health-300">
                    {{ ($panel['count'] ?? 0) > 0 ? $panel['count'].' at risk' : 'View' }}
                </a>
            @endif
        </div>
    </div>

    @if(count($items))
        <ol class="flex-1 divide-y divide-line">
            @foreach($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="flex items-start gap-2.5 px-3.5 py-2.5 transition hover:bg-canvas">
                        <span class="mt-0.5 w-4 shrink-0 text-[11px] font-semibold tabular-nums text-ink-muted">{{ $loop->iteration }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-semibold text-ink">{{ $item['label'] }}</p>
                                <span @class([
                                    'module-badge shrink-0',
                                    'bg-rose-50 text-rose-800' => ($item['tone'] ?? '') === 'red',
                                    'bg-amber-50 text-amber-900' => ($item['tone'] ?? '') === 'amber',
                                ])>{{ $item['status_label'] }}</span>
                            </div>
                            <p class="mt-0.5 truncate text-[11px] text-muted">{{ $item['detail'] }}</p>
                        </div>
                    </a>
                </li>
            @endforeach
        </ol>
    @else
        <div class="flex flex-1 items-center px-3.5 py-6 text-sm text-muted">
            No medicines at risk.
        </div>
    @endif
</div>
