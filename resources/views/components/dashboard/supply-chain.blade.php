@props(['highlight' => null])

@php
$nodes = config('portal.supply_chain', []);
$nodeIcons = [
    'ndoh' => 'shield',
    'lae_ams' => 'warehouse',
    'modilon_hospital' => 'hospital',
];
@endphp

<div class="dashboard-supply-chain">
    <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-section-label">Supply chain</p>
            <h3 class="heading-section">PNG medicine flow</h3>
            <p class="mt-1 max-w-xl text-sm text-muted">NDoH procures nationally, ships to Lae AMS, then dispenses at Modilon Hospital.</p>
        </div>
        @if($highlight)
            <span class="badge-brand mt-2 sm:mt-0">You are here: {{ collect($nodes)->firstWhere('key', $highlight)['label'] ?? 'Portal' }}</span>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_auto_1fr_auto_1fr] sm:items-stretch">
        @foreach($nodes as $index => $node)
            <div @class([
                'dashboard-chain-node',
                'dashboard-chain-node-active' => $highlight === $node['key'],
            ])>
                <div class="flex items-center gap-3">
                    <div @class([
                        'dashboard-chain-icon',
                        'bg-brand-600 text-white shadow-soft' => $highlight === $node['key'],
                        'bg-surface-muted text-ink-muted dark:bg-zinc-800 dark:text-zinc-400' => $highlight !== $node['key'],
                    ])>
                        <x-dashboard.icon :name="$nodeIcons[$node['key']] ?? 'cube'" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 text-left">
                        <p @class(['text-sm font-bold', 'text-brand-700 dark:text-brand-300' => $highlight === $node['key'], 'text-ink dark:text-zinc-200' => $highlight !== $node['key']])>{{ $node['label'] }}</p>
                        <p class="text-xs text-muted">{{ $node['description'] }}</p>
                    </div>
                </div>
                @if($highlight === $node['key'])
                    <span class="mt-3 inline-flex rounded-full bg-brand-600 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">Your role</span>
                @endif
            </div>

            @if($index < count($nodes) - 1)
                <div class="hidden items-center justify-center text-brand-400 sm:flex">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            @endif
        @endforeach
    </div>
</div>
