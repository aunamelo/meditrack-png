<x-app-layout>
    <x-slot name="header"><div><p class="text-section-label">Reports</p><h2 class="heading-page">Regional Report</h2></div></x-slot>
    <x-page-container>
        <div class="surface-panel p-6">
            <form method="GET" class="mb-8 flex flex-wrap items-end gap-4">
                <div><label class="mb-1 block text-sm font-medium">From</label><input type="date" name="date_from" value="{{ $from->toDateString() }}" class="rounded-md border-gray-300"></div>
                <div><label class="mb-1 block text-sm font-medium">To</label><input type="date" name="date_to" value="{{ $to->toDateString() }}" class="rounded-md border-gray-300"></div>
                <button type="submit" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Generate</button>
            </form>
            <p class="mb-6 text-sm text-gray-500">Period: {{ $report['period']['from'] }} — {{ $report['period']['to'] }}</p>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['Lae AMS batches', $report['inventory']['total_batches'], 'teal'],
                    ['Units on hand', number_format($report['inventory']['total_units']), 'teal'],
                    ['Hospital orders', $report['hospital_orders']['total'], 'amber'],
                    ['Pending hospital orders', $report['hospital_orders']['pending'], 'amber'],
                    ['Rejected orders', $report['hospital_orders']['rejected'], 'red'],
                    ['NDoH receipts', $report['ndoh_receipts']['received'].' / '.$report['ndoh_receipts']['total'], 'blue'],
                    ['Hospital road deliveries', $report['hospital_shipments']['total'], 'purple'],
                    ['Open discrepancies', $report['discrepancies']['open'], 'red'],
                ] as [$label, $value, $tone])
                    <div class="rounded-xl border border-gray-200 p-4">
                        <p class="text-xs font-semibold uppercase text-gray-500">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div>
                    <h4 class="mb-3 font-semibold">Hospital order breakdown</h4>
                    <ul class="space-y-2 text-sm">
                        @foreach($report['hospital_orders'] as $key => $count)
                            @if($key !== 'total')<li class="flex justify-between"><span>{{ ucfirst(str_replace('_',' ', $key)) }}</span><span class="font-semibold">{{ $count }}</span></li>@endif
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="mb-3 font-semibold">Road delivery summary</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex justify-between"><span>Units dispatched by road</span><span class="font-semibold">{{ number_format($report['hospital_shipments']['units_sent']) }}</span></li>
                        <li class="flex justify-between"><span>In transit</span><span class="font-semibold">{{ $report['hospital_shipments']['in_transit'] }}</span></li>
                        <li class="flex justify-between"><span>Delivered</span><span class="font-semibold">{{ $report['hospital_shipments']['delivered'] }}</span></li>
                        <li class="flex justify-between"><span>NDoH units received</span><span class="font-semibold">{{ number_format($report['ndoh_receipts']['units_received']) }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
