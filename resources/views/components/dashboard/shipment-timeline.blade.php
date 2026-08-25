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
                <a href="{{ $panel['more_url'] }}" class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-health-700 hover:underline dark:text-health-300">
                    View all
                    <x-dashboard.icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            @endif
        </div>
        <div class="mt-2 flex flex-wrap gap-2 text-[10px] font-semibold uppercase tracking-wide">
            <span class="rounded bg-amber-50 px-1.5 py-0.5 text-amber-800">Ordered</span>
            <span class="rounded bg-blue-50 px-1.5 py-0.5 text-blue-800">In transit</span>
            <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-emerald-800">Received</span>
        </div>
    </div>

    @if(count($items))
        <ol class="flex-1 divide-y divide-line">
            @foreach($items as $item)
                <li>
                    @if(! empty($item['url']))
                        <a href="{{ $item['url'] }}" class="dashboard-recent-item">
                    @else
                        <div class="dashboard-recent-item">
                    @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-ink dark:text-zinc-100">{{ $item['title'] }}</p>
                            <p class="mt-0.5 truncate text-[11px] text-muted">
                                {{ $item['subtitle'] }}
                                @if(! empty($item['vehicle']))
                                    · {{ $item['vehicle'] }}
                                @endif
                                @if(! empty($item['when']))
                                    · {{ $item['when'] }}
                                @endif
                            </p>
                        </div>
                        <span @class([
                            'dashboard-status-pill shrink-0',
                            'bg-amber-50 text-amber-800' => ($item['stage'] ?? '') === 'ordered',
                            'bg-blue-50 text-blue-800' => ($item['stage'] ?? '') === 'in_transit',
                            'bg-emerald-50 text-emerald-800' => ($item['stage'] ?? '') === 'received',
                        ])>{{ $item['meta'] }}</span>
                    @if(! empty($item['url']))
                        </a>
                    @else
                        </div>
                    @endif
                </li>
            @endforeach
        </ol>
    @else
        <p class="px-3.5 py-8 text-center text-sm text-muted">No hospital road deliveries yet.</p>
    @endif
</div>
