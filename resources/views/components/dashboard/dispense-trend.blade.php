@props(['panel'])

@php
    $points = $panel['points'] ?? [];
@endphp

<div class="medcare-panel overflow-hidden">
    <div class="border-b border-line/80 px-5 py-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-brand-200">Activity</p>
                <h3 class="mt-1 font-display text-base font-bold text-ink">{{ $panel['title'] }}</h3>
                <p class="mt-0.5 text-xs text-muted">{{ $panel['subtitle'] }}</p>
            </div>
            @if(! empty($panel['more_url']))
                <a href="{{ $panel['more_url'] }}" class="shrink-0 text-xs font-semibold text-brand-600 hover:underline">Records</a>
            @endif
        </div>
    </div>

    <div class="flex items-end gap-1 px-5 py-5" style="min-height: 7.5rem">
        @foreach($points as $point)
            <div class="group relative flex min-w-0 flex-1 flex-col items-center justify-end gap-1">
                <span class="pointer-events-none absolute -top-6 hidden rounded bg-ink px-1.5 py-0.5 text-[10px] font-semibold text-white group-hover:block">
                    {{ $point['value'] }}
                </span>
                <div
                    class="w-full max-w-[1.25rem] rounded-t-md transition"
                    @class([
                        'bg-brand-600' => $point['is_today'] ?? false,
                        'bg-brand-300 hover:bg-brand-500' => ! ($point['is_today'] ?? false),
                    ])
                    style="height: {{ max(($point['value'] ?? 0) > 0 ? 10 : 4, (int) round((($point['percent'] ?? 0) / 100) * 88)) }}px"
                    title="{{ $point['label'] }}: {{ $point['value'] }}"
                ></div>
            </div>
        @endforeach
    </div>
    <div class="flex justify-between border-t border-line/70 px-5 py-2 text-[10px] text-muted">
        <span>{{ $points[0]['label'] ?? '' }}</span>
        <span>{{ $points[count($points) - 1]['label'] ?? '' }}</span>
    </div>
</div>
