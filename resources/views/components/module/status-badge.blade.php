@props(['variant' => 'default', 'label'])

@php
    // Domain status → restrained clinical palette (no rainbow of Tailwind blues/purples).
    $classes = match ($variant) {
        'active', 'received', 'approved', 'fx_cleared' => 'bg-health-50 text-health-800 dark:bg-health-950/50 dark:text-health-300',
        'expiring_soon', 'partial', 'pending', 'customs' => 'bg-amber-50 text-amber-900 dark:bg-amber-950/50 dark:text-amber-300',
        'expired', 'cancelled', 'rejected' => 'bg-rose-50 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300',
        'low_stock' => 'bg-orange-50 text-orange-900 dark:bg-orange-950/50 dark:text-orange-300',
        'ordered', 'manufacturing', 'sent', 'shipped' => 'bg-brand-50 text-brand-800 dark:bg-brand-950/50 dark:text-brand-300',
        default => 'bg-surface-muted text-ink-secondary dark:bg-zinc-800 dark:text-zinc-300',
    };
@endphp

<span {{ $attributes->merge(['class' => "module-badge {$classes}"]) }}>{{ $label }}</span>
