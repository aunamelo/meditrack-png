@props(['variant' => 'default', 'label'])

@php
    $classes = match ($variant) {
        'active', 'received', 'approved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300',
        'expiring_soon', 'partial', 'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300',
        'expired', 'cancelled', 'rejected' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300',
        'low_stock' => 'bg-orange-100 text-orange-800 dark:bg-orange-950/50 dark:text-orange-300',
        'ordered', 'sent', 'shipped' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300',
        default => 'bg-surface-muted text-ink-secondary dark:bg-zinc-800 dark:text-zinc-300',
    };
@endphp

<span {{ $attributes->merge(['class' => "module-badge {$classes}"]) }}>{{ $label }}</span>
