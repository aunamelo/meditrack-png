@props(['label', 'value', 'hint' => null, 'tone' => 'teal', 'url' => '#', 'icon' => null, 'featured' => false, 'trend' => null])

@php
$iconName = $icon ?? match($tone) {
    'amber' => 'bell',
    'blue' => 'truck',
    'red' => 'shield',
    default => 'cube',
};
$iconWrap = match($tone) {
    'amber' => 'bg-amber-50 text-amber-600',
    'blue' => 'bg-sky-50 text-sky-600',
    'red' => 'bg-rose-50 text-rose-600',
    'slate' => 'bg-canvas text-ink-muted',
    default => 'bg-health-50 text-health-700',
};
@endphp

<a href="{{ $url }}" @class([
    'group block transition hover:-translate-y-0.5',
    'medcare-stat-featured' => $featured,
    'medcare-stat-card' => ! $featured,
])>
    <div class="flex items-start justify-between gap-3">
        <div @class([
            'flex h-11 w-11 items-center justify-center rounded-2xl',
            'bg-white/20 text-white' => $featured,
            $iconWrap => ! $featured,
        ])>
            <x-dashboard.icon :name="$iconName" class="h-5 w-5" />
        </div>
        @if($trend)
            <span @class([
                'rounded-full px-2 py-0.5 text-[11px] font-bold',
                'bg-white/20 text-white' => $featured,
                'bg-emerald-50 text-emerald-600' => ! $featured && (str_starts_with((string) $trend, '+') || str_starts_with((string) $trend, '↑')),
                'bg-rose-50 text-rose-600' => ! $featured && ! str_starts_with((string) $trend, '+') && ! str_starts_with((string) $trend, '↑'),
            ])>{{ $trend }}</span>
        @endif
    </div>
    <p @class([
        'mt-4 font-display text-3xl font-bold tracking-tight',
        'text-white' => $featured,
        'text-ink' => ! $featured,
    ])>{{ $value }}</p>
    <p @class([
        'mt-1 text-sm font-semibold',
        'text-white/90' => $featured,
        'text-ink-secondary' => ! $featured,
    ])>{{ $label }}</p>
    @if($hint)
        <p @class(['mt-0.5 text-xs', 'text-white/70' => $featured, 'text-muted' => ! $featured])>{{ $hint }}</p>
    @endif
</a>
