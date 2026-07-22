@props(['config'])

<div {{ $attributes->merge(['class' => 'surface-panel overflow-hidden']) }} x-data="dashboardChart(@js($config))">
    <div class="border-b border-line px-5 py-4 dark:border-zinc-800">
        <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $config['title'] }}</h3>
        @if(! empty($config['subtitle']))
            <p class="mt-0.5 text-xs text-muted">{{ $config['subtitle'] }}</p>
        @endif
    </div>

    <div class="relative h-56 px-2 py-4">
        <canvas x-ref="canvas" aria-label="{{ $config['title'] }}"></canvas>
    </div>
</div>
