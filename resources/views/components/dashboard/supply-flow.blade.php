@props(['panel'])

@php
    $nodes = $panel['nodes'] ?? [];
    $max = max(1, (int) ($panel['max'] ?? 1));
@endphp

<div class="medcare-panel overflow-hidden">
    <div class="border-b border-line px-3.5 py-2.5">
        <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $panel['title'] }}</h3>
        @if(! empty($panel['subtitle']))
            <p class="mt-0.5 text-[11px] text-muted">{{ $panel['subtitle'] }}</p>
        @endif
    </div>

    @if(! empty($panel['empty']))
        <div class="flex flex-col items-center justify-center px-4 py-12 text-center">
            <x-dashboard.icon name="package" class="mb-3 h-10 w-10 text-gray-400" />
            <p class="text-sm font-medium text-gray-500">No data available yet.</p>
            <p class="mt-1 text-xs text-gray-400">Receive inventory to populate this chart.</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 px-3.5 py-4 sm:grid-cols-4">
            @foreach($nodes as $index => $node)
                @php $percent = (int) round((((int) ($node['value'] ?? 0)) / $max) * 100); @endphp
                <div class="relative min-w-0">
                    <div class="rounded-lg border border-line bg-canvas px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900/60">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted">{{ $node['detail'] }}</p>
                        <p class="mt-1 truncate text-sm font-semibold text-ink">{{ $node['label'] }}</p>
                        <p class="mt-1 text-lg font-bold tabular-nums text-health-800 dark:text-health-300">{{ number_format((int) $node['value']) }}</p>
                        <p class="text-[10px] text-muted">units</p>
                        <div class="mt-2 h-1.5 overflow-hidden rounded bg-white dark:bg-zinc-800">
                            <div class="h-full rounded bg-health-700" style="width: {{ max(6, $percent) }}%"></div>
                        </div>
                    </div>
                    @if(! $loop->last)
                        <p class="pointer-events-none absolute -right-2 top-1/2 hidden -translate-y-1/2 text-health-700 sm:block" aria-hidden="true">→</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
