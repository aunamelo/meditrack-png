@props([
    'counts' => [],
    'title' => 'Orders in import pipeline',
])

@php
    $items = [
        ['key' => 'manufacturing', 'label' => 'Manufacturing', 'tone' => 'blue'],
        ['key' => 'shipped', 'label' => 'In transit', 'tone' => 'purple'],
        ['key' => 'customs', 'label' => 'Customs', 'tone' => 'amber'],
        ['key' => 'fx_cleared', 'label' => 'FX cleared', 'tone' => 'teal'],
    ];
@endphp

<section {{ $attributes->merge(['class' => 'module-panel p-6']) }}>
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-section-label">Procurement</p>
            <h3 class="heading-section">{{ $title }}</h3>
        </div>
        <a href="{{ getDashboardOrderRoute('index') }}" class="module-table-link text-sm">View all orders</a>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        <div class="rounded-xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-800 dark:bg-brand-950/30">
            <p class="text-xs font-bold uppercase tracking-wide text-muted">Total in pipeline</p>
            <p class="mt-1 font-display text-2xl font-bold text-brand-700 dark:text-brand-300">{{ $counts['total'] ?? 0 }}</p>
        </div>
        @foreach($items as $item)
            <div class="rounded-xl border border-line bg-surface p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-bold uppercase tracking-wide text-muted">{{ $item['label'] }}</p>
                <p class="mt-1 font-display text-2xl font-bold text-ink dark:text-zinc-100">{{ $counts[$item['key']] ?? 0 }}</p>
            </div>
        @endforeach
    </div>
</section>
