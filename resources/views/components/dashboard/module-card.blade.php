@props(['label', 'description', 'url', 'primary' => false, 'icon' => 'cube'])

<a href="{{ $url }}"
   @class([
       'dashboard-module-card group',
       'dashboard-module-card-primary' => $primary,
   ])>
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
</a>
