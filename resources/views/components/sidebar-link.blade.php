@props([
    'href' => '#',
    'active' => false,
    'icon' => 'home',
    'label' => '',
    'description' => null,
    'badge' => null,
])

@php
$classes = $active
    ? 'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-800 dark:bg-brand-950/50 dark:text-brand-300'
    : 'border-transparent text-ink-muted hover:border-line hover:bg-surface-muted hover:text-ink dark:text-zinc-400 dark:hover:border-zinc-800 dark:hover:bg-zinc-900 dark:hover:text-zinc-100';
@endphp

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => "group flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm font-semibold transition {$classes}"]) }}>
    <span @class([
        'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition',
        'bg-brand-600 text-white shadow-glow dark:bg-brand-500' => $active,
        'bg-surface-muted text-ink-faint group-hover:bg-brand-50 group-hover:text-brand-600 dark:bg-zinc-800 dark:text-zinc-500 dark:group-hover:bg-brand-950 dark:group-hover:text-brand-400' => ! $active,
    ])>
        @include('components.icons.'.$icon)
    </span>
    <span class="min-w-0 flex-1">
        <span class="flex items-center gap-2">
            <span class="truncate">{{ $label }}</span>
            @if($badge)
                <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $badge }}</span>
            @endif
        </span>
        @if($description)
            <span class="block truncate text-xs font-medium opacity-80">{{ $description }}</span>
        @endif
    </span>
</a>
