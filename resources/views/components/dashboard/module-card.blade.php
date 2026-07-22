@props(['label', 'description', 'url', 'primary' => false, 'icon' => 'cube'])

<a href="{{ $url }}"
   @class([
       'dashboard-module-card group',
       'dashboard-module-card-primary' => $primary,
   ])>
    <div class="flex items-start gap-4">
        <div @class([
            'dashboard-module-icon',
            'bg-white/15 text-white' => $primary,
            'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300' => ! $primary,
        ])>
            <x-dashboard.icon :name="$icon" class="h-5 w-5" />
        </div>
        <div class="min-w-0 flex-1">
            <span @class(['block font-display text-base font-bold tracking-tight', 'text-white' => $primary, 'text-ink dark:text-zinc-50' => ! $primary])>{{ $label }}</span>
            <span @class(['mt-1 block text-sm', 'text-brand-50/90' => $primary, 'text-muted' => ! $primary])>{{ $description }}</span>
        </div>
    </div>
    <span @class(['mt-5 inline-flex items-center text-xs font-bold uppercase tracking-wider', 'text-white/90' => $primary, 'text-brand-600 dark:text-brand-400' => ! $primary])>
        Open
        <svg class="ml-1.5 h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </span>
</a>
