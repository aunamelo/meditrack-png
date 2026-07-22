@props(['config'])

<div {{ $attributes->merge(['class' => 'medcare-panel overflow-hidden']) }} x-data="dashboardChart(@js($config))">
    <div class="flex items-center justify-between border-b border-line/80 px-5 py-4">
        <div>
            <h3 class="font-display text-base font-bold text-ink">{{ $config['title'] }}</h3>
            @if(! empty($config['subtitle']))
                <p class="mt-0.5 text-xs text-muted">{{ $config['subtitle'] }}</p>
            @endif
        </div>
        <div class="hidden rounded-xl bg-canvas p-1 sm:flex">
            <span class="rounded-lg bg-surface px-3 py-1 text-[11px] font-semibold text-brand-700 shadow-sm">Month</span>
            <span class="px-3 py-1 text-[11px] font-medium text-muted">Week</span>
            <span class="px-3 py-1 text-[11px] font-medium text-muted">Year</span>
        </div>
    </div>

    <div class="relative h-64 px-2 py-4 sm:h-72">
        <canvas x-ref="canvas" aria-label="{{ $config['title'] }}"></canvas>
    </div>
</div>
