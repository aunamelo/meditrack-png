@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-brand-500 bg-brand-50 py-2 ps-3 pe-4 text-start text-base font-semibold text-brand-700 focus:border-brand-600 focus:bg-brand-50 focus:outline-none dark:bg-brand-950/40 dark:text-brand-300'
            : 'block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-ink-muted transition hover:border-line hover:bg-surface-muted hover:text-ink focus:border-line focus:bg-surface-muted focus:text-ink focus:outline-none dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:bg-zinc-900 dark:hover:text-zinc-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
