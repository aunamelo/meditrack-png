@props(['config'])

<div {{ $attributes->merge(['class' => 'surface-panel px-5 py-4']) }} x-data="dashboardChart(@js($config))">
    <div class="mb-3">
        <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $config['title'] }}</h3>
        @if(! empty($config['subtitle']))
            <p class="mt-0.5 text-xs text-muted">{{ $config['subtitle'] }}</p>
        @endif
    </div>

    <div class="relative h-52">
        <canvas x-ref="canvas" aria-label="{{ $config['title'] }}"></canvas>
    </div>
</div>
