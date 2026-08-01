@props([
    'counts' => [],
    'title' => 'Orders in import pipeline',
])

@php
    $items = [
        ['key' => 'manufacturing', 'label' => 'Manufacturing', 'tone' => 'bg-brand-600'],
        ['key' => 'shipped', 'label' => 'In transit', 'tone' => 'bg-brand-800'],
        ['key' => 'customs', 'label' => 'Customs', 'tone' => 'bg-amber-600'],
        ['key' => 'fx_cleared', 'label' => 'FX cleared', 'tone' => 'bg-health-700'],
    ];
    $total = max(1, (int) ($counts['total'] ?? 0));
@endphp

<section {{ $attributes->merge(['class' => 'module-panel p-3.5']) }}>
    <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
        <div>
            <p class="text-section-label">Procurement</p>
            <h3 class="heading-section">{{ $title }}</h3>
        </div>
        <a href="{{ getDashboardOrderRoute('index') }}" class="module-table-link text-xs">View all orders</a>
    </div>

    <div class="mb-3 flex h-2.5 w-full overflow-hidden rounded bg-canvas dark:bg-zinc-800" role="img" aria-label="Pipeline stage mix">
        @foreach($items as $item)
            @php $value = (int) ($counts[$item['key']] ?? 0); @endphp
            @if($value > 0)
                <a
                    href="{{ getDashboardOrderRoute('index').'?status='.$item['key'] }}"
                    class="{{ $item['tone'] }} transition hover:opacity-90"
                    style="width: {{ max(2, round(($value / $total) * 100)) }}%"
                    title="{{ $item['label'] }}: {{ $value }}"
                ><span class="sr-only">{{ $item['label'] }}: {{ $value }}</span></a>
            @endif
        @endforeach
    </div>

    <div class="grid grid-cols-2 gap-2 lg:grid-cols-5">
        <a href="{{ getDashboardOrderRoute('index') }}" class="rounded border border-line bg-canvas px-3 py-2 transition hover:border-health-600/40 dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">In pipeline</p>
            <p class="mt-0.5 font-display text-xl font-semibold tabular-nums text-ink dark:text-zinc-100">{{ $counts['total'] ?? 0 }}</p>
        </a>
        @foreach($items as $item)
            <a href="{{ getDashboardOrderRoute('index').'?status='.$item['key'] }}" class="rounded border border-line bg-surface px-3 py-2 transition hover:border-health-600/40 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">{{ $item['label'] }}</p>
                <p class="mt-0.5 font-display text-xl font-semibold tabular-nums text-ink dark:text-zinc-100">{{ $counts[$item['key']] ?? 0 }}</p>
            </a>
        @endforeach
    </div>
</section>
