<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Reports</p>
            <h2 class="heading-page">Hospital Report</h2>
        </div>
    </x-slot>

    <x-page-container>
        <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm text-slate-700">
            Modilon General Hospital summary for pharmacy operations — inventory, Lae AMS requests, deliveries, dispensing, discrepancies, and stock takes.
        </div>

        <div class="surface-panel p-6">
            <form method="GET" class="mb-8 flex flex-wrap items-end gap-4">
                <div>
                    <label for="date_from" class="mb-1 block text-sm font-medium">From</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $from->toDateString() }}" class="rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-sm font-medium">To</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $to->toDateString() }}" class="rounded-md border-gray-300 text-sm">
                </div>
                <button type="submit" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Generate</button>
            </form>

            <p class="mb-6 text-sm text-gray-500">Period: {{ $report['period']['from'] }} — {{ $report['period']['to'] }}</p>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
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
                    <div class="rounded-xl border border-gray-200 p-4">
                        <p class="text-xs font-semibold uppercase text-gray-500">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <h3 class="mb-3 font-semibold">Request status breakdown</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach($report['requests'] as $key => $count)
                            @if(! in_array($key, ['total', 'units_requested'], true))
                                <li class="flex justify-between"><span>{{ ucfirst(str_replace('_', ' ', $key)) }}</span><span class="font-semibold">{{ $count }}</span></li>
                            @endif
                        @endforeach
                        <li class="flex justify-between border-t pt-2"><span>Units requested</span><span class="font-semibold">{{ number_format($report['requests']['units_requested']) }}</span></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-3 font-semibold">Deliveries &amp; quality</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between"><span>In transit from Lae AMS</span><span class="font-semibold">{{ $report['deliveries']['in_transit'] }}</span></li>
                        <li class="flex justify-between"><span>Units dispatched</span><span class="font-semibold">{{ number_format($report['deliveries']['units_dispatched']) }}</span></li>
                        <li class="flex justify-between"><span>Discrepancies total</span><span class="font-semibold">{{ $report['discrepancies']['total'] }}</span></li>
                        <li class="flex justify-between"><span>Discrepancies resolved</span><span class="font-semibold">{{ $report['discrepancies']['resolved'] }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
