<div class="mb-2 text-xs font-bold uppercase tracking-widest text-teal-700 dark:text-teal-300">{{ $report['facility'] }}</div>
<p class="mb-1 text-sm text-slate-500 dark:text-slate-400">
    Period: {{ $report['period']['from'] }} — {{ $report['period']['to'] }}
    · Generated {{ $report['generated_at'] }} by {{ $report['generated_by'] }}
</p>

<div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Stock batches', $report['inventory']['total_batches']],
        ['Units on hand', number_format($report['inventory']['total_units'])],
        ['Expiring (6 mo)', $report['inventory']['expiring_soon']],
        ['Low stock batches', $report['inventory']['low_stock_batches']],
        ['Requests filed', $report['requests']['total']],
        ['Requests pending', $report['requests']['pending']],
        ['Deliveries received', $report['deliveries']['received'].' / '.$report['deliveries']['total']],
        ['Units dispensed', number_format($report['dispensing']['units_dispensed'])],
        ['Dispensing records', $report['dispensing']['records']],
        ['Open discrepancies', $report['discrepancies']['open']],
        ['Stock takes', $report['stock_takes']['total']],
        ['Stock take net Δ', number_format($report['stock_takes']['net_delta'])],
    ] as [$label, $value])
        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <p class="text-xs font-semibold uppercase text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div>
        <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Request status breakdown</h3>
        <ul class="space-y-2 text-sm">
            @foreach($report['requests'] as $key => $count)
                @if(! in_array($key, ['total', 'units_requested'], true))
                    <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $key)) }}</span><span class="font-semibold">{{ $count }}</span></li>
                @endif
            @endforeach
            <li class="flex justify-between gap-4 border-t border-slate-200 pt-2 dark:border-slate-700"><span>Units requested</span><span class="font-semibold">{{ number_format($report['requests']['units_requested']) }}</span></li>
        </ul>
    </div>
    <div>
        <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Deliveries &amp; quality</h3>
        <ul class="space-y-2 text-sm">
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">In transit from Lae AMS</span><span class="font-semibold">{{ $report['deliveries']['in_transit'] }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Units dispatched</span><span class="font-semibold">{{ number_format($report['deliveries']['units_dispatched']) }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Discrepancies total</span><span class="font-semibold">{{ $report['discrepancies']['total'] }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Discrepancies resolved</span><span class="font-semibold">{{ $report['discrepancies']['resolved'] }}</span></li>
        </ul>
    </div>
</div>

<div class="mt-10">
    <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Recent Lae AMS requests</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700">
                    <th class="py-2 pr-3">Order</th>
                    <th class="py-2 pr-3">Medicine</th>
                    <th class="py-2 pr-3">Qty</th>
                    <th class="py-2 pr-3">Status</th>
                    <th class="py-2">Filed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['recent_requests'] as $order)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="py-2 pr-3 font-medium">{{ $order->order_number }}</td>
                        <td class="py-2 pr-3">{{ $order->drug_name }} {{ $order->dosage }}</td>
                        <td class="py-2 pr-3 tabular-nums">{{ number_format($order->quantity_requested) }}</td>
                        <td class="py-2 pr-3">{{ ucfirst($order->status) }}</td>
                        <td class="py-2">{{ $order->created_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-3 text-slate-500">No requests in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-8">
    <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Recent dispensing</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700">
                    <th class="py-2 pr-3">Date</th>
                    <th class="py-2 pr-3">Medicine</th>
                    <th class="py-2 pr-3">Patient</th>
                    <th class="py-2">Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['recent_dispensing'] as $record)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="py-2 pr-3">{{ $record->dispensed_at?->format('M d, Y') }}</td>
                        <td class="py-2 pr-3">{{ $record->drug->drug_name ?? '—' }}</td>
                        <td class="py-2 pr-3">{{ $record->patient?->full_name ?: '—' }}</td>
                        <td class="py-2 tabular-nums">{{ number_format($record->quantity_dispensed) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-3 text-slate-500">No dispensing records in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
