@props([
    'counts' => [],
    'title' => 'Orders in import pipeline',
])

@php
    $items = [
        ['key' => 'manufacturing', 'label' => 'Manufacturing', 'tone' => 'bg-sky-500'],
        ['key' => 'shipped', 'label' => 'In transit', 'tone' => 'bg-violet-500'],
        ['key' => 'customs', 'label' => 'Customs', 'tone' => 'bg-amber-500'],
        ['key' => 'fx_cleared', 'label' => 'FX cleared', 'tone' => 'bg-teal-500'],
    ];
    $total = max(1, (int) ($counts['total'] ?? 0));
@endphp

<section {{ $attributes->merge(['class' => 'module-panel p-6']) }}>
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-section-label">Procurement</p>
            <h3 class="heading-section">{{ $title }}</h3>
        </div>
        <a href="{{ getDashboardOrderRoute('index') }}" class="module-table-link text-sm">View all orders</a>
    </div>

    {{-- Visual funnel --}}
    <div class="mb-5 flex h-4 w-full overflow-hidden rounded-full bg-canvas dark:bg-zinc-800" role="img" aria-label="Pipeline stage mix">
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

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        <a href="{{ getDashboardOrderRoute('index') }}" class="rounded-xl border border-brand-200 bg-brand-50 p-4 transition hover:border-brand-400 dark:border-brand-800 dark:bg-brand-950/30">
            <p class="text-xs font-bold uppercase tracking-wide text-muted">Total in pipeline</p>
            <p class="mt-1 font-display text-2xl font-bold text-brand-700 dark:text-brand-300">{{ $counts['total'] ?? 0 }}</p>
        </a>
        @foreach($items as $item)
            <a href="{{ getDashboardOrderRoute('index').'?status='.$item['key'] }}" class="rounded-xl border border-line bg-surface p-4 transition hover:border-brand-300 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-bold uppercase tracking-wide text-muted">{{ $item['label'] }}</p>
                <p class="mt-1 font-display text-2xl font-bold text-ink dark:text-zinc-100">{{ $counts[$item['key']] ?? 0 }}</p>
            </a>
        @endforeach
    </div>
</section>
