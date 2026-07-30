<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Inventory</p>
            <h2 class="heading-page">Stock Takes</h2>
        </div>
    </x-slot>

    <x-page-container>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-muted">Record physical counts and correct on-hand quantities so system stock matches the shelf.</p>
            <a href="{{ getDashboardStockAdjustmentRoute('create') }}" class="btn-brand text-xs uppercase tracking-wider">New stock take</a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="surface-panel overflow-hidden">
            <form method="GET" class="border-b border-gray-100 p-4">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search number, medicine, or batch…" class="input-field max-w-md">
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Number</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Medicine / batch</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-600">System</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-600">Counted</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-600">Delta</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600">Reason</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-600">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($adjustments as $adjustment)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <a href="{{ getDashboardStockAdjustmentRoute('show', $adjustment) }}" class="font-semibold text-brand-700 hover:underline">{{ $adjustment->adjustment_number }}</a>
                                </td>
                                <td class="px-4 py-2.5">
                                    {{ $adjustment->drug?->drug_name }} ({{ $adjustment->drug?->dosage }})
                                    <span class="block text-xs text-muted">Batch {{ $adjustment->drug?->batch_number }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($adjustment->quantity_system) }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($adjustment->quantity_counted) }}</td>
                                <td @class([
                                    'px-4 py-2.5 text-right tabular-nums font-semibold',
                                    'text-rose-700' => $adjustment->quantity_delta < 0,
                                    'text-emerald-700' => $adjustment->quantity_delta > 0,
                                ])>
                                    {{ $adjustment->quantity_delta > 0 ? '+' : '' }}{{ number_format($adjustment->quantity_delta) }}
                                </td>
                                <td class="px-4 py-2.5">{{ $adjustment->reasonLabel() }}</td>
                                <td class="px-4 py-2.5 text-muted">{{ $adjustment->adjusted_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-muted">No stock takes recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($adjustments->hasPages())
                <div class="border-t border-gray-100 p-4">{{ $adjustments->links() }}</div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
