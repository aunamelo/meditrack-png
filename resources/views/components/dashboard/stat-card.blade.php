@props(['label', 'value', 'hint' => null, 'tone' => 'teal', 'url' => '#'])

@php
$accent = match($tone) {
    'amber' => 'border-l-amber-500',
    'blue' => 'border-l-sky-500',
    'red' => 'border-l-rose-500',
    'slate' => 'border-l-zinc-400 dark:border-l-zinc-500',
    default => 'border-l-brand-500',
};
@endphp

<a href="{{ $url }}" class="stat-health group block border-l-[3px] {{ $accent }} transition hover:bg-brand-50/50 dark:hover:bg-zinc-800/40">
    <p class="text-sm font-medium text-ink-muted dark:text-zinc-400">{{ $label }}</p>
    <p class="mt-1 font-display text-2xl font-semibold text-ink dark:text-zinc-50">{{ $value }}</p>
    @if($hint)
        <p class="mt-0.5 text-xs text-ink-faint dark:text-zinc-500">{{ $hint }}</p>
    @endif
</a>
