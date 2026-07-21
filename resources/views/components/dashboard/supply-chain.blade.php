@props(['highlight' => null])

@php
$nodes = config('portal.supply_chain', []);
@endphp

<div class="surface-panel p-6">
    <div class="mb-4">
        <h3 class="heading-section">PNG medicine supply chain</h3>
        <p class="mt-1 text-xs font-medium text-ink-muted dark:text-zinc-500">National procurement flows through Lae AMS to hospital dispensing</p>
    </div>

    <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
        @foreach($nodes as $index => $node)
            <div @class([
                'flex-1 rounded-xl border p-4 text-center transition',
                'border-brand-500 bg-brand-50 ring-2 ring-brand-500/20 dark:border-brand-600 dark:bg-brand-950/40' => $highlight === $node['key'],
                'border-line bg-surface-muted dark:border-zinc-800 dark:bg-zinc-900/60' => $highlight !== $node['key'],
            ])>
                <p @class(['text-sm font-bold', 'text-brand-700 dark:text-brand-300' => $highlight === $node['key'], 'text-ink-secondary dark:text-zinc-300' => $highlight !== $node['key']])>{{ $node['label'] }}</p>
                <p class="mt-1 text-xs font-medium text-ink-muted dark:text-zinc-500">{{ $node['description'] }}</p>
                @if($highlight === $node['key'])
                    <span class="mt-2 inline-flex rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white dark:bg-brand-500">Your scope</span>
                @endif
            </div>

            @if($index < count($nodes) - 1)
                <div class="flex items-center justify-center text-ink-faint dark:text-zinc-600 sm:px-1">
                    <svg class="h-5 w-5 rotate-90 sm:rotate-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            @endif
        @endforeach
    </div>
</div>
