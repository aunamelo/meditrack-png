@props(['label', 'value', 'hint', 'tone' => 'teal', 'url' => '#'])

@php
$toneClasses = match($tone) {
    'amber' => 'from-amber-500 to-orange-500',
    'blue' => 'from-blue-500 to-indigo-500',
    'red' => 'from-red-500 to-rose-500',
    'slate' => 'from-slate-500 to-gray-600',
    default => 'from-brand-500 to-brand-700',
};
@endphp

<a href="{{ $url }}" class="surface-card-hover group block p-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-ink-muted dark:text-zinc-400">{{ $label }}</p>
            <p class="mt-2 font-display text-3xl font-bold tracking-display text-ink dark:text-zinc-50">{{ $value }}</p>
            <p class="mt-1 text-xs font-medium text-ink-faint dark:text-zinc-500">{{ $hint }}</p>
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $toneClasses }} text-white shadow-glow">
            <svg class="h-5 w-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </div>
</a>
