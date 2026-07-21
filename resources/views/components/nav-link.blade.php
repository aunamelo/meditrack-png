@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-brand-500 px-1 pt-1 text-sm font-semibold leading-5 text-brand-700 focus:border-brand-600 dark:text-brand-300'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-ink-muted transition hover:border-line hover:text-ink dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:text-zinc-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
