<div class="mb-2 text-xs font-bold uppercase tracking-widest text-teal-700 dark:text-teal-300">{{ $report['facility'] }}</div>
<p class="mb-1 text-sm text-slate-500 dark:text-slate-400">
    Period: {{ $report['period']['from'] }} — {{ $report['period']['to'] }}
    · Generated {{ $report['generated_at'] }} by {{ $report['generated_by'] }}
</p>

<div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['NDoH batches', $report['inventory']['total_batches']],
        ['NDoH units on hand', number_format($report['inventory']['total_units'])],
        ['Low stock batches', $report['inventory']['low_stock_batches']],
        ['Expiring soon', $report['inventory']['expiring_soon']],
        ['Procurement orders', $report['orders']['total']],
        ['Orders pending approval', $report['orders']['pending']],
        ['Orders received', $report['orders']['received']],
        ['Units ordered', number_format($report['orders']['units_ordered'])],
        ['Shipments to Lae AMS', $report['shipments']['total']],
        ['Shipments awaiting approval', $report['shipments']['pending']],
        ['In transit to Lae', $report['shipments']['sent']],
        ['Received at Lae AMS', $report['shipments']['received']],
    ] as [$label, $value])
        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <p class="text-xs font-semibold uppercase text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $value }}</p>
        </div>
    @endforeach
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
        <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Logistics &amp; stock takes</h3>
        <ul class="space-y-2 text-sm">
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Units awaiting shipment approval</span><span class="font-semibold">{{ number_format($report['shipments']['units_pending']) }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Units in transit to Lae</span><span class="font-semibold">{{ number_format($report['shipments']['units_in_transit']) }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Units received at Lae</span><span class="font-semibold">{{ number_format($report['shipments']['units_received_at_lae']) }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Supplier units received</span><span class="font-semibold">{{ number_format($report['orders']['units_received']) }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Stock takes / net Δ</span><span class="font-semibold">{{ $report['stock_takes']['total'] }} / {{ number_format($report['stock_takes']['net_delta']) }}</span></li>
        </ul>
    </div>
</div>

<div class="mt-8">
    <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Procurement pipeline</h3>
    <ul class="grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
        @foreach(['pending','manufacturing','shipped','customs','fx_cleared','partial','received','cancelled'] as $status)
            <li class="flex justify-between gap-2 rounded-lg border border-slate-200 px-3 py-2 dark:border-slate-700">
                <span class="text-slate-600 dark:text-slate-300">{{ str_replace('_', ' ', ucfirst($status)) }}</span>
                <span class="font-semibold">{{ $report['orders'][$status] }}</span>
            </li>
        @endforeach
    </ul>
</div>

<div class="mt-10">
    <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Recent procurement orders</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700">
                    <th class="py-2 pr-3">Order</th>
                    <th class="py-2 pr-3">Items</th>
                    <th class="py-2 pr-3">Status</th>
                    <th class="py-2 pr-3">Created by</th>
                    <th class="py-2">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['recent_orders'] as $order)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="py-2 pr-3 font-medium">{{ $order->order_number }}</td>
                        <td class="py-2 pr-3">{{ $order->itemsSummary() }}</td>
                        <td class="py-2 pr-3">{{ $order->statusLabel() }}</td>
                        <td class="py-2 pr-3">{{ $order->creator->name ?? '—' }}</td>
                        <td class="py-2">{{ $order->created_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-3 text-slate-500">No procurement orders in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-8">
    <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Recent shipments to Lae AMS</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700">
                    <th class="py-2 pr-3">Shipment</th>
                    <th class="py-2 pr-3">Medicine</th>
                    <th class="py-2 pr-3">Qty</th>
                    <th class="py-2 pr-3">Status</th>
                    <th class="py-2">Ship date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['recent_shipments'] as $shipment)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="py-2 pr-3 font-medium">{{ $shipment->transfer_number }}</td>
                        <td class="py-2 pr-3">{{ $shipment->drug->drug_name ?? '—' }}</td>
                        <td class="py-2 pr-3 tabular-nums">{{ number_format($shipment->quantity_sent) }}</td>
                        <td class="py-2 pr-3">{{ ndohToLaeAmsTransferStatusLabel($shipment->status) }}</td>
                        <td class="py-2">{{ $shipment->sent_date?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-3 text-slate-500">No shipments to Lae AMS in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
