<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Stock</p>
            <h2 class="heading-page">Stock Movements</h2>
        </div>
    </x-slot>

    <x-page-container>
        <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm text-slate-700">
            Track what entered and left {{ $levelLabel }} — receipts, transfers, dispensing, and stock takes.
        </div>

        <div class="surface-panel p-6">
            <form method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">From</label>
                    <input type="date" name="date_from" value="{{ $from->toDateString() }}" class="rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">To</label>
                    <input type="date" name="date_to" value="{{ $to->toDateString() }}" class="rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Search</label>
                    <input type="search" name="search" value="{{ $search }}" placeholder="Medicine or reference…" class="rounded-md border-gray-300 text-sm">
                </div>
                <button type="submit" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Update</button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <caption class="sr-only">Stock movements for {{ $levelLabel }} from {{ $from->toDateString() }} to {{ $to->toDateString() }}</caption>
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left font-semibold text-gray-600">When</th>
                            <th scope="col" class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                            <th scope="col" class="px-3 py-2 text-left font-semibold text-gray-600">Medicine</th>
                            <th scope="col" class="px-3 py-2 text-right font-semibold text-gray-600">Qty</th>
                            <th scope="col" class="px-3 py-2 text-left font-semibold text-gray-600">Reference</th>
                            <th scope="col" class="px-3 py-2 text-left font-semibold text-gray-600">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($movements as $row)
                            <tr>
                                <td class="px-3 py-2.5 whitespace-nowrap text-muted">{{ $row['occurred_at']?->format('d M Y H:i') }}</td>
                                <td class="px-3 py-2.5">
                                    <span @class([
                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                        'bg-emerald-100 text-emerald-800' => $row['direction'] === 'in',
                                        'bg-rose-100 text-rose-800' => $row['direction'] === 'out',
                                    ])>{{ $row['type_label'] }}</span>
                                </td>
                                <td class="px-3 py-2.5 font-medium">
                                    {{ $row['medicine'] }}
                                    @if($row['batch'])
                                        <span class="block text-xs text-muted">Batch {{ $row['batch'] }}</span>
                                    @endif
                                </td>
                                <td @class([
                                    'px-3 py-2.5 text-right font-semibold tabular-nums',
                                    'text-emerald-700' => $row['direction'] === 'in',
                                    'text-rose-700' => $row['direction'] === 'out',
                                ])>
                                    {{ $row['direction'] === 'in' ? '+' : '−' }}{{ number_format($row['quantity']) }}
                                </td>
                                <td class="px-3 py-2.5 tabular-nums">{{ $row['reference'] }}</td>
                                <td class="px-3 py-2.5 text-muted">{{ $row['notes'] ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-muted">No movements in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
