@props(['config'])

@php
    $isEmpty = ! empty($config['empty'])
        || empty($config['labels'] ?? [])
        || collect($config['datasets'] ?? [])->every(fn ($dataset) => array_sum(array_map('floatval', $dataset['data'] ?? [])) === 0.0);
    $emptyMessage = $config['empty_message'] ?? 'Receive inventory to populate this chart.';
@endphp

<div {{ $attributes->merge(['class' => 'medcare-panel overflow-hidden']) }} @unless($isEmpty) x-data="dashboardChart(@js($config))" @endunless>
    <div class="border-b border-line px-3.5 py-2.5">
        <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $config['title'] }}</h3>
        @if(! empty($config['subtitle']))
            <p class="mt-0.5 text-[11px] text-muted">{{ $config['subtitle'] }}</p>
        @endif
    </div>

    @if($isEmpty)
        <div class="flex flex-col items-center justify-center px-4 py-12 text-center">
            <x-dashboard.icon name="package" class="mb-3 h-10 w-10 text-gray-400" />
            <p class="text-sm font-medium text-gray-500">No data available yet.</p>
            <p class="mt-1 text-xs text-gray-400">{{ $emptyMessage }}</p>
        </div>
    @else
        <div class="relative h-56 px-2 py-3 sm:h-64">
            <canvas x-ref="canvas" aria-label="{{ $config['title'] }}"></canvas>
        </div>
    @endif
</div>
