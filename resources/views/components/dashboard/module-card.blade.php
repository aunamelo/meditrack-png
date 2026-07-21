@props(['label', 'description', 'url', 'primary' => false])

<a href="{{ $url }}"
   @class([
       'group flex flex-col rounded-xl border p-5 transition hover:shadow-glow',
       'border-brand-600 bg-brand-600 text-white hover:bg-brand-700 dark:border-brand-500 dark:bg-brand-600' => $primary,
       'surface-card-hover' => ! $primary,
   ])>
    <span @class(['font-display text-base font-bold tracking-tight', 'text-white' => $primary, 'text-ink dark:text-zinc-50' => ! $primary])>{{ $label }}</span>
    <span @class(['mt-1 text-sm font-medium', 'text-brand-50' => $primary, 'text-ink-muted dark:text-zinc-400' => ! $primary])>{{ $description }}</span>
    <span @class(['mt-4 inline-flex items-center text-xs font-bold uppercase tracking-wider', 'text-white/90' => $primary, 'text-brand-600 dark:text-brand-400' => ! $primary])>
        Open module
        <svg class="ml-1 h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </span>
</a>
