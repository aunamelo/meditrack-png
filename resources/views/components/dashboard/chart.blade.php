@props(['config'])

<div {{ $attributes->merge(['class' => 'surface-panel p-6']) }} x-data="dashboardChart(@js($config))">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <h3 class="heading-section">{{ $config['title'] }}</h3>
            @if(! empty($config['subtitle']))
                <p class="mt-1 text-sm font-medium text-muted">{{ $config['subtitle'] }}</p>
            @endif
        </div>
    </div>

    <div class="relative h-64 sm:h-72">
        <canvas x-ref="canvas" aria-label="{{ $config['title'] }}"></canvas>
    </div>
</div>
