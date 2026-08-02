<div class="mb-2 text-xs font-bold uppercase tracking-widest text-teal-700 dark:text-teal-300">{{ $report['facility'] }}</div>
<p class="mb-1 text-sm text-slate-500 dark:text-slate-400">
    Period: {{ $report['period']['from'] }} — {{ $report['period']['to'] }}
    · Generated {{ $report['generated_at'] }} by {{ $report['generated_by'] }}
</p>
<p class="mb-1 text-sm text-slate-500 dark:text-slate-400">
    Stock figures are on hand now. Order, receipt, delivery, and discrepancy counts use the selected period (cancelled road deliveries excluded).
</p>

<div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Lae AMS batches (now)', $report['inventory']['total_batches']],
        ['Units on hand (now)', number_format($report['inventory']['total_units'])],
        ['Low stock batches', $report['inventory']['low_stock_batches']],
        ['Expiring soon', $report['inventory']['expiring_soon']],
        ['Hospital orders', $report['hospital_orders']['total']],
        ['Pending hospital orders', $report['hospital_orders']['pending']],
        ['Rejected orders', $report['hospital_orders']['rejected']],
        ['NDoH shipments received', $report['ndoh_receipts']['received'].' / '.$report['ndoh_receipts']['total']],
        ['Hospital road deliveries', $report['hospital_shipments']['total']],
        ['Open discrepancies', $report['discrepancies']['open']],
        ['Hospital units dispatched', number_format($report['hospital_shipments']['units_sent'])],
        ['NDoH units received', number_format($report['ndoh_receipts']['units_received'])],
    ] as [$label, $value])
        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <p class="text-xs font-semibold uppercase text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div>
        <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Hospital order breakdown</h3>
        <ul class="space-y-2 text-sm">
            @foreach($report['hospital_orders'] as $key => $count)
                @if($key !== 'total')
                    <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">{{ ucfirst(str_replace('_',' ', $key)) }}</span><span class="font-semibold">{{ $count }}</span></li>
                @endif
            @endforeach
        </ul>
    </div>
    <div>
        <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Logistics summary</h3>
        <ul class="space-y-2 text-sm">
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Hospital deliveries in transit</span><span class="font-semibold">{{ $report['hospital_shipments']['in_transit'] }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Hospital deliveries completed</span><span class="font-semibold">{{ $report['hospital_shipments']['delivered'] }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">NDoH shipments awaiting receipt</span><span class="font-semibold">{{ $report['ndoh_receipts']['awaiting'] }}</span></li>
            <li class="flex justify-between gap-4"><span class="text-slate-600 dark:text-slate-300">Discrepancies resolved</span><span class="font-semibold">{{ $report['discrepancies']['resolved'] }}</span></li>
        </ul>
    </div>
</div>

<div class="mt-10">
    <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Recent hospital orders</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700">
                    <th class="py-2 pr-3">Order</th>
                    <th class="py-2 pr-3">Medicine</th>
                    <th class="py-2 pr-3">Qty</th>
                    <th class="py-2 pr-3">Status</th>
                    <th class="py-2">Requested</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['recent_hospital_orders'] as $order)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="py-2 pr-3 font-medium">{{ $order->order_number }}</td>
                        <td class="py-2 pr-3">{{ $order->drug_name }} {{ $order->dosage }}</td>
                        <td class="py-2 pr-3 tabular-nums">{{ number_format($order->quantity_requested) }}</td>
                        <td class="py-2 pr-3">{{ ucfirst($order->status) }}</td>
                        <td class="py-2">{{ $order->created_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-3 text-slate-500">No hospital orders in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-8">
    <h3 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">Recent hospital shipments</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700">
                    <th class="py-2 pr-3">Shipment</th>
                    <th class="py-2 pr-3">Medicine</th>
                    <th class="py-2 pr-3">Qty</th>
                    <th class="py-2 pr-3">Status</th>
                    <th class="py-2">Sent</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['recent_shipments'] as $shipment)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="py-2 pr-3 font-medium">{{ $shipment->transfer_number }}</td>
                        <td class="py-2 pr-3">{{ $shipment->drug->drug_name ?? $shipment->hospitalOrder->drug_name ?? '—' }}</td>
                        <td class="py-2 pr-3 tabular-nums">{{ number_format($shipment->quantity_sent) }}</td>
                        <td class="py-2 pr-3">{{ ucfirst($shipment->status) }}</td>
                        <td class="py-2">{{ $shipment->sent_date?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-3 text-slate-500">No hospital shipments in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
