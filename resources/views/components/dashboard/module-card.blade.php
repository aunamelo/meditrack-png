@props(['label', 'description', 'url', 'primary' => false, 'icon' => 'cube'])

<a href="{{ $url }}"
   @class([
       'dashboard-module-card group',
       'dashboard-module-card-primary' => $primary,
   ])>
    <span @class([
        'mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg',
        'bg-white/15 text-white' => $primary,
        'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300' => ! $primary,
    ]) aria-hidden="true">
        <x-dashboard.icon :name="$icon" class="h-5 w-5" />
    </span>
    <div class="min-w-0 flex-1">
        <span @class([
            'block text-sm font-semibold tracking-tight',
            'text-white' => $primary,
            'text-ink dark:text-zinc-50' => ! $primary,
        ])>{{ $label }}</span>
        <span @class([
            'mt-0.5 block text-xs leading-snug',
            'text-white/85' => $primary,
            'text-muted' => ! $primary,
        ])>{{ $description }}</span>
    </div>
    <x-dashboard.icon
        name="arrow-right"
        @class([
            'h-4 w-4 shrink-0 transition group-hover:translate-x-0.5',
            'text-white/80' => $primary,
            'text-teal-600 dark:text-teal-400' => ! $primary,
        ])
    />
</a>
