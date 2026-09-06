<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">{{ $ward->name }}</h2>
            <p class="text-sm text-muted">{{ $ward->ward_number }}</p>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardWardRoute('index')" label="Back to wards" class="mb-6" />

        <div class="space-y-6">
            <!-- Ward Details -->
            <div class="module-panel p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-ink">Ward Details</h3>
                    <div class="flex gap-2">
                        <a href="{{ getDashboardWardRoute('edit', $ward) }}" class="btn-module-secondary text-sm">Edit</a>
                        <form action="{{ getDashboardWardRoute('destroy', $ward) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ward?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-module-danger text-sm">Delete</button>
                        </form>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                    <p><span class="font-medium text-muted">Type</span><br>{{ $ward->typeLabel() }}</p>
                    <p><span class="font-medium text-muted">Location</span><br>{{ $ward->location ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Contact Person</span><br>{{ $ward->contact_person ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Contact Phone</span><br>{{ $ward->contact_phone ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Bed Capacity</span><br>{{ $ward->bed_capacity ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Status</span><br>
                        <x-module.status-badge
                            :variant="$ward->is_active ? 'received' : 'cancelled'"
                            :label="$ward->is_active ? 'Active' : 'Inactive'"
                        />
                    </p>
                </div>
            </div>

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

            <!-- Current Stock -->
            <div class="module-panel p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-ink">Current Stock</h3>
                    <a href="{{ getDashboardWardStockRoute('show', $ward) }}" class="text-sm text-brand-600 hover:underline">View all stock →</a>
                </div>
                @if($ward->wardStocks->isNotEmpty())
                    <div class="module-table-wrap overflow-x-auto">
                        <table class="module-table">
                            <thead>
                                <tr>
                                    <th>Drug</th>
                                    <th>Batch</th>
                                    <th>On Hand</th>
                                    <th>Reorder Point</th>
                                    <th>Last Issued</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ward->wardStocks->take(5) as $stock)
                                    <tr>
                                        <td>{{ $stock->drug->drug_name }}</td>
                                        <td>{{ $stock->drug->batch_number }}</td>
                                        <td class="font-semibold {{ $stock->isLowStock() ? 'text-amber-600' : 'text-ink' }}">
                                            {{ number_format($stock->quantity_on_hand) }} {{ $stock->drug->unit }}
                                        </td>
                                        <td>{{ number_format($stock->reorder_point) }}</td>
                                        <td>{{ $stock->last_issued_at?->format('d M Y') ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-muted">No stock in this ward yet.</p>
                @endif
            </div>

            <!-- Recent Activity -->
            <div class="module-panel p-6">
                <h3 class="text-lg font-semibold text-ink mb-4">Recent Activity</h3>
                <div class="space-y-4">
                    @if($ward->drugIssuances->isNotEmpty())
                        <div>
                            <p class="text-xs font-medium text-muted uppercase tracking-wider mb-2">Recent Issuances</p>
                            <div class="space-y-2">
                                @foreach($ward->drugIssuances->take(3) as $issuance)
                                    <div class="flex justify-between items-center text-sm">
                                        <span>{{ $issuance->drug->drug_name }} ({{ number_format($issuance->quantity_issued) }})</span>
                                        <span class="text-muted">{{ $issuance->issuance_date->format('d M Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($ward->drugUsages->isNotEmpty())
                        <div>
                            <p class="text-xs font-medium text-muted uppercase tracking-wider mb-2">Recent Usage</p>
                            <div class="space-y-2">
                                @foreach($ward->drugUsages->take(3) as $usage)
                                    <div class="flex justify-between items-center text-sm">
                                        <span>{{ $usage->drug->drug_name }} ({{ number_format($usage->quantity_used) }})</span>
                                        <span class="text-muted">{{ $usage->usage_date->format('d M Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($ward->drugIssuances->isEmpty() && $ward->drugUsages->isEmpty())
                        <p class="text-sm text-muted">No recent activity.</p>
                    @endif
                </div>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
