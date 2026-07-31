<div class="mb-2 text-xs font-bold uppercase tracking-widest text-teal-700 dark:text-teal-300">{{ $report['facility'] }}</div>
<p class="mb-1 text-sm text-slate-500 dark:text-slate-400">
    Period: {{ $report['period']['from'] }} — {{ $report['period']['to'] }}
    · Generated {{ $report['generated_at'] }} by {{ $report['generated_by'] }}
</p>

<div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Amount spent (PGK)', 'K '.number_format($report['spending']['amount_spent'], 2)],
        ['Committed spend (PGK)', 'K '.number_format($report['spending']['amount_committed'], 2)],
        ['In pipeline (PGK)', 'K '.number_format($report['spending']['amount_in_pipeline'], 2)],
        ['Pending approval (PGK)', 'K '.number_format($report['spending']['amount_pending_approval'], 2)],
        ['NDoH inventory value', 'K '.number_format($report['spending']['inventory_value'], 2)],
        ['Procurement orders', $report['orders']['total']],
        ['Shipments to Lae AMS', $report['shipments']['total']],
        ['NDoH units on hand', number_format($report['inventory']['total_units'])],
    ] as [$label, $value])
        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <p class="text-xs font-semibold uppercase text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $value }}</p>
        </div>
    @endforeach
</div>

<p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
    Amount spent = invoice totals for received/partial orders. Committed excludes cancelled. Inventory value = batch cost × units on hand.
</p>

<div class="mt-8">
    <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Spend by supplier (PGK)</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700">
                    <th class="py-2 pr-3">Supplier</th>
                    <th class="py-2 pr-3">Orders</th>
                    <th class="py-2 text-right">Amount (PGK)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['spending']['by_supplier'] as $row)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="py-2 pr-3 font-medium">{{ $row['supplier'] }}</td>
                        <td class="py-2 pr-3 tabular-nums">{{ $row['orders'] }}</td>
                        <td class="py-2 text-right tabular-nums">K {{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-3 text-slate-500">No invoice amounts recorded for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div>
        <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Corridor stock on hand</h3>
        <ul class="space-y-2 text-sm">
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">NDoH central</span><span class="font-semibold">{{ number_format($report['corridor']['ndoh_units']) }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Lae AMS</span><span class="font-semibold">{{ number_format($report['corridor']['lae_ams_units']) }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Modilon Hospital</span><span class="font-semibold">{{ number_format($report['corridor']['modilon_units']) }}</span></li>
        </ul>
    </div>
    <div>
        <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Shipments to Lae AMS</h3>
        <ul class="space-y-2 text-sm">
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Awaiting approval</span><span class="font-semibold">{{ $report['shipments']['pending'] }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">In transit</span><span class="font-semibold">{{ $report['shipments']['sent'] }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Received at Lae</span><span class="font-semibold">{{ $report['shipments']['received'] }}</span></li>
        </ul>
    </div>
</div>

<div class="mt-10">
    <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Recent procurement orders</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700">
                    <th class="py-2 pr-3">Order</th>
                    <th class="py-2 pr-3">Items</th>
                    <th class="py-2 pr-3">Invoice (PGK)</th>
                    <th class="py-2 pr-3">Status</th>
                    <th class="py-2">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['recent_orders'] as $order)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="py-2 pr-3 font-medium">{{ $order->order_number }}</td>
                        <td class="py-2 pr-3">{{ $order->itemsSummary() }}</td>
                        <td class="py-2 pr-3 tabular-nums">{{ $order->invoice_amount !== null ? 'K '.number_format((float) $order->invoice_amount, 2) : '—' }}</td>
                        <td class="py-2 pr-3">{{ $order->statusLabel() }}</td>
                        <td class="py-2">{{ $order->created_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-3 text-slate-500">No procurement orders in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
