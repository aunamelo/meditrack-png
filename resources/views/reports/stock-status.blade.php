<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Stock</p>
            <h2 class="heading-page">Stock Status</h2>
        </div>
    </x-slot>

    <x-page-container>
        <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm text-slate-700 dark:border-brand-900 dark:bg-brand-950/30 dark:text-slate-300">
            View how much medicine is currently in stock, which items are running low or out of stock, and the suggested quantity to reorder.
            Use this report to decide when Modilon should request replenishment from Lae AMS, or when national procurement is needed.
        </div>

        <div class="surface-panel p-6">
            <form method="GET" class="mb-8 flex flex-wrap items-end gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Facility</label>
                    <select name="level" class="rounded-md border-gray-300 text-sm">
                        @foreach($allowedLevels as $option)
                            <option value="{{ $option }}" @selected($level === $option)>{{ $levelLabels[$option] ?? $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">From</label>
                    <input type="date" name="date_from" value="{{ $from->toDateString() }}" class="rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">To</label>
                    <input type="date" name="date_to" value="{{ $to->toDateString() }}" class="rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Status</label>
                    <select name="status" class="rounded-md border-gray-300 text-sm">
                        <option value="all" @selected($statusFilter === 'all')>All</option>
                        <option value="stock_out" @selected($statusFilter === 'stock_out')>Stock-out</option>
                        <option value="critical" @selected($statusFilter === 'critical')>Critical</option>
                        <option value="low" @selected($statusFilter === 'low')>Low</option>
                        <option value="adequate" @selected($statusFilter === 'adequate')>Adequate</option>
                        <option value="overstock" @selected($statusFilter === 'overstock')>Overstock</option>
                    </select>
                </div>
                <button type="submit" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Update</button>
            </form>

            <div class="mb-8 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach([
                    ['Stock-out', $counts['stock_out'], 'red'],
                    ['Critical', $counts['critical'], 'red'],
                    ['Low', $counts['low'], 'amber'],
                    ['Adequate+', $counts['adequate'], 'teal'],
                ] as [$label, $value, $tone])
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-slate-700">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <p class="mb-4 text-sm text-gray-500">
                Showing {{ $levelLabels[$level] ?? $level }} · consumption {{ $from->toDateString() }} — {{ $to->toDateString() }}
            </p>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <caption class="sr-only">Stock status by medicine for {{ $levelLabels[$level] ?? $level }}</caption>
                    <thead class="bg-gray-50 dark:bg-slate-800">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left font-semibold text-gray-600">Medicine</th>
                            <th scope="col" class="px-3 py-2 text-right font-semibold text-gray-600">On hand</th>
                            <th scope="col" class="px-3 py-2 text-right font-semibold text-gray-600">Consumed</th>
                            <th scope="col" class="px-3 py-2 text-right font-semibold text-gray-600">AMC</th>
                            <th scope="col" class="px-3 py-2 text-right font-semibold text-gray-600">Days of stock</th>
                            <th scope="col" class="px-3 py-2 text-left font-semibold text-gray-600">Status</th>
                            @if($level === 'corridor')
                                <th scope="col" class="px-3 py-2 text-right font-semibold text-gray-600">On order</th>
                            @endif
                            <th scope="col" class="px-3 py-2 text-right font-semibold text-gray-600">Suggested qty</th>
                            @if($canRequestFromLae)
                                <th scope="col" class="px-3 py-2 text-right font-semibold text-gray-600">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @forelse($rows as $row)
                            <tr>
                                <td class="px-3 py-2.5 font-medium text-gray-900 dark:text-slate-100">{{ $row['label'] }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['stock_on_hand']) }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['consumed']) }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['amc'], 1) }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">
                                    {{ $row['days_of_stock'] === null ? '—' : number_format($row['days_of_stock'], 1) }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <span @class([
                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                        'bg-rose-100 text-rose-800' => in_array($row['status'], ['stock_out', 'critical'], true),
                                        'bg-amber-100 text-amber-800' => $row['status'] === 'low',
                                        'bg-sky-100 text-sky-800' => $row['status'] === 'overstock',
                                        'bg-emerald-100 text-emerald-800' => $row['status'] === 'adequate',
                                    ])>{{ $row['status_label'] }}</span>
                                </td>
                                @if($level === 'corridor')
                                    <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['pending_on_order'] ?? 0) }}</td>
                                @endif
                                <td class="px-3 py-2.5 text-right font-semibold tabular-nums text-brand-700">
                                    {{ number_format($row['suggested_quantity']) }}
                                </td>
                                @if($canRequestFromLae)
                                    <td class="px-3 py-2.5 text-right">
                                        @if($row['suggested_quantity'] > 0 || in_array($row['status'], ['stock_out', 'critical', 'low'], true))
                                            <form action="{{ getDashboardHospitalOrderRoute('store') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="drug_name" value="{{ $row['drug_name'] }}">
                                                <input type="hidden" name="dosage" value="{{ $row['dosage'] }}">
                                                <input type="hidden" name="quantity_requested" value="{{ max(1, (int) $row['suggested_quantity']) }}">
                                                <input type="hidden" name="notes" value="Requested from Stock Status on {{ now()->format('d M Y') }}. Status: {{ $row['status_label'] }}. AMC: {{ number_format($row['amc'], 1) }}. Suggested: {{ number_format($row['suggested_quantity']) }} units.">
                                                <button type="submit" class="text-xs font-semibold text-brand-700 hover:underline">
                                                    Request from Lae AMS
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-muted">—</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ ($level === 'corridor' ? 8 : 7) + ($canRequestFromLae ? 1 : 0) }}" class="px-3 py-8 text-center text-gray-500">
                                    No stock or consumption data for this selection yet. Dispense medicines or receive inventory to populate the report.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
