@props(['panel'])

@php
    $items = $panel['items'] ?? [];
@endphp

<div class="medcare-panel overflow-hidden">
    <div class="border-b border-line/80 px-5 py-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-brand-200">FEFO</p>
                <h3 class="mt-1 font-display text-base font-bold text-ink">{{ $panel['title'] }}</h3>
                <p class="mt-0.5 text-xs text-muted">{{ $panel['subtitle'] }}</p>
            </div>
            @if(! empty($panel['more_url']))
                <a href="{{ $panel['more_url'] }}" class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-brand-600 hover:underline">
                    View all
                    <x-dashboard.icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            @endif
        </div>
    </div>

    @if(count($items))
        <ul class="divide-y divide-line/70">
            @foreach($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="block px-5 py-3.5 transition hover:bg-canvas/80">
                        <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-ink">{{ $item['label'] }}</p>
                                <p class="text-xs text-muted">Batch {{ $item['batch'] }} · {{ $item['qty'] }}</p>
                            </div>
                            <div class="text-right">
                                <p @class([
                                    'text-xs font-bold',
                                    'text-rose-700' => in_array($item['urgency'], ['expired', 'urgent'], true),
                                    'text-amber-700' => $item['urgency'] === 'soon',
                                    'text-brand-700' => $item['urgency'] === 'watch',
                                ])>{{ $item['days_label'] }}</p>
                                <p class="text-[11px] text-muted">Exp {{ $item['expiry'] }}</p>
                            </div>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-canvas dark:bg-zinc-800">
                            <div
                                class="h-full rounded-full"
                                @class([
                                    'bg-rose-500' => in_array($item['urgency'], ['expired', 'urgent'], true),
                                    'bg-amber-500' => $item['urgency'] === 'soon',
                                    'bg-brand-500' => $item['urgency'] === 'watch',
                                ])
                                style="width: {{ max(3, (int) $item['percent']) }}%"
                            ></div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="flex flex-wrap gap-3 border-t border-line/70 px-5 py-2 text-[10px] font-semibold uppercase tracking-wide text-muted">
            <span><span class="mr-1 inline-block h-2 w-2 rounded-full bg-rose-500"></span> Red &lt; 30 days</span>
            <span><span class="mr-1 inline-block h-2 w-2 rounded-full bg-amber-500"></span> Yellow 30–90 days</span>
            <span><span class="mr-1 inline-block h-2 w-2 rounded-full bg-brand-500"></span> Green &gt; 90 days</span>
        </div>
    @else
        <div class="px-5 py-10 text-center text-sm text-muted">
            No batches expiring in the next 6 months.
        </div>
    @endif
</div>
