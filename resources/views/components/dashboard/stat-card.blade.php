@props(['label', 'value', 'hint' => null, 'tone' => 'teal', 'url' => '#', 'icon' => null])

@php
$iconName = $icon ?? match($tone) {
    'amber' => 'bell',
    'blue' => 'truck',
    'red' => 'shield',
    default => 'cube',
};
$iconWrap = match($tone) {
    'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
    'blue' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
    'red' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
    'slate' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
    default => 'bg-brand-100 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300',
};
@endphp

<a href="{{ $url }}" class="dashboard-stat-card group">
    <div class="flex items-start justify-between gap-3">
        <div @class(['dashboard-stat-icon', $iconWrap])>
            <x-dashboard.icon :name="$iconName" class="h-5 w-5" />
        </div>
        <svg class="h-4 w-4 shrink-0 text-ink-faint opacity-0 transition group-hover:translate-x-0.5 group-hover:opacity-100 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
    <p class="mt-4 font-display text-3xl font-bold tracking-tight text-ink dark:text-zinc-50">{{ $value }}</p>
    <p class="mt-1 text-sm font-semibold text-ink-secondary dark:text-zinc-200">{{ $label }}</p>
    @if($hint)
        <p class="mt-0.5 text-xs text-muted">{{ $hint }}</p>
    @endif
</a>
