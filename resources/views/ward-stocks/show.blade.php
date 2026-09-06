<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">{{ $ward->name }} - Stock</h2>
            <p class="text-sm text-muted">{{ $ward->ward_number }}</p>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardWardRoute('show', $ward)" label="Back to ward" class="mb-6" />

        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <a href="{{ getDashboardDrugIssuanceRoute('create') }}?ward_id={{ $ward->id }}" class="module-panel p-4 hover:border-brand-300 transition-colors cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-ink">Issue Drugs</p>
                            <p class="text-xs text-muted">Transfer from pharmacy</p>
                        </div>
                    </div>
                </a>

                <a href="{{ getDashboardDrugReturnRoute('create') }}?ward_id={{ $ward->id }}" class="module-panel p-4 hover:border-brand-300 transition-colors cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-ink">Return Drugs</p>
                            <p class="text-xs text-muted">Send back to pharmacy</p>
                        </div>
                    </div>
                </a>

                <a href="{{ getDashboardDrugUsageRoute('create') }}?ward_id={{ $ward->id }}" class="module-panel p-4 hover:border-brand-300 transition-colors cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-ink">Log Usage</p>
                            <p class="text-xs text-muted">Record administration</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Stock List -->
            <div class="module-panel p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-ink">Current Stock</h3>
                    <span class="text-sm text-muted">{{ $wardStocks->count() }} items</span>
                </div>
                @if($wardStocks->isNotEmpty())
                    <div class="module-table-wrap overflow-x-auto">
                        <table class="module-table">
                            <thead>
                                <tr>
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
                                @foreach($wardStocks as $stock)
                                    <tr>
                                        <td>{{ $stock->drug->drug_name }}</td>
                                        <td>{{ $stock->drug->batch_number }}</td>
                                        <td class="font-semibold {{ $stock->isLowStock() ? 'text-amber-600' : 'text-ink' }}">
                                            {{ number_format($stock->quantity_on_hand) }} {{ $stock->drug->unit }}
                                        </td>
                                        <td>
                                            <form action="{{ getDashboardWardStockRoute('update-reorder-point', $stock) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="reorder_point" value="{{ $stock->reorder_point }}" min="0" class="w-20 rounded border border-gray-300 px-2 py-1 text-sm">
                                                <button type="submit" class="text-xs text-brand-600 hover:underline">Update</button>
                                            </form>
                                        </td>
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
                                                <a href="{{ getDashboardDrugIssuanceRoute('create') }}?ward_id={{ $ward->id }}&drug_id={{ $stock->drug->id }}" class="module-table-action">Issue</a>
                                                <a href="{{ getDashboardDrugUsageRoute('create') }}?ward_id={{ $ward->id }}&drug_id={{ $stock->drug->id }}" class="module-table-action">Log Usage</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-muted">No stock in this ward yet. Issue drugs to begin tracking.</p>
                @endif
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="module-panel p-4">
                    <p class="text-sm text-muted">Total Items</p>
                    <p class="text-2xl font-semibold text-ink">{{ $wardStocks->count() }}</p>
                </div>
                <div class="module-panel p-4">
                    <p class="text-sm text-muted">Low Stock Items</p>
                    <p class="text-2xl font-semibold text-amber-600">{{ $wardStocks->filter(fn($s) => $s->isLowStock())->count() }}</p>
                </div>
                <div class="module-panel p-4">
                    <p class="text-sm text-muted">Out of Stock</p>
                    <p class="text-2xl font-semibold text-red-600">{{ $wardStocks->filter(fn($s) => $s->quantity_on_hand === 0)->count() }}</p>
                </div>
                <div class="module-panel p-4">
                    <p class="text-sm text-muted">Total Quantity</p>
                    <p class="text-2xl font-semibold text-ink">{{ number_format($wardStocks->sum('quantity_on_hand')) }}</p>
                </div>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
