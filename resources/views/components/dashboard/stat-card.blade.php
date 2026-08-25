@props(['label', 'value', 'hint' => null, 'tone' => 'teal', 'url' => '#', 'icon' => null, 'featured' => false, 'trend' => null])

@php
$toneBar = match($tone) {
    'amber' => 'border-l-amber-600',
    'blue' => 'border-l-brand-600',
    'red' => 'border-l-rose-600',
    'slate' => 'border-l-slate-400',
    default => 'border-l-health-700',
};
$numericValue = is_numeric(str_replace(',', '', (string) $value))
    ? (float) str_replace(',', '', (string) $value)
    : null;
$isZero = $numericValue !== null && $numericValue == 0.0;
@endphp

<a href="{{ $url }}" @class([
    'group block border-l-[3px]',
    'medcare-stat-featured' => $featured,
    'medcare-stat-card' => ! $featured,
    $toneBar,
])>
    <div class="flex items-baseline justify-between gap-3">
        <p @class([
            'font-display text-2xl font-semibold tabular-nums tracking-tight',
            'text-ink dark:text-zinc-50' => ! $isZero,
            'text-gray-400 dark:text-zinc-500' => $isZero,
        ])>{{ $value }}</p>
        @if($trend)
            <span class="text-[11px] font-semibold tabular-nums text-ink-muted">{{ $trend }}</span>
        @endif
    </div>
    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.08em] text-ink-secondary dark:text-zinc-300">{{ $label }}</p>
    @if($isZero)
        <p class="mt-1 text-xs text-gray-400">No data yet</p>
    @elseif($hint)
        <p class="mt-0.5 text-[11px] text-muted">{{ $hint }}</p>
    @endif
</a>
