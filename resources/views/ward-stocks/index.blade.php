<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Ward Inventory</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="warehouse"
            description="Monitor drug stock levels across all hospital wards."
        />

        <div class="module-panel p-6">
            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Ward</th>
                            <th>Drug</th>
                            <th>Batch</th>
                            <th>On Hand</th>
                            <th>Reorder Point</th>
                            <th>Status</th>
                            <th>Last Issued</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wardStocks as $stock)
                            <tr>
                                <td>{{ $stock->ward->name }}</td>
                                <td>{{ $stock->drug->drug_name }}</td>
                                <td>{{ $stock->drug->batch_number }}</td>
                                <td class="font-semibold {{ $stock->isLowStock() ? 'text-amber-600' : 'text-ink' }}">
                                    {{ number_format($stock->quantity_on_hand) }} {{ $stock->drug->unit }}
                                </td>
                                <td>{{ number_format($stock->reorder_point) }}</td>
                                <td>
                                    @if($stock->isLowStock())
                                        <x-module.status-badge variant="amber" label="Low Stock" />
                                    @elseif($stock->quantity_on_hand === 0)
                                        <x-module.status-badge variant="red" label="Out of Stock" />
                                    @else
                                        <x-module.status-badge variant="received" label="OK" />
                                    @endif
                                </td>
                                <td>{{ $stock->last_issued_at?->format('d M Y') ?: '—' }}</td>
                                <td class="text-right">
                                    <div class="module-table-actions">
                                        <a href="{{ getDashboardWardStockRoute('show', $stock->ward) }}" class="module-table-action">View Ward</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-module.empty-row
                                :colspan="8"
                                title="No ward stock found"
                                description="Issue drugs to wards to begin tracking inventory."
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $wardStocks->links() }}</div>
        </div>
    </x-page-container>
</x-app-layout>
