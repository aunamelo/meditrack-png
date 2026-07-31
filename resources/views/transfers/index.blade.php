<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Logistics</p>
            <h2 class="heading-page">Shipments to Lae AMS</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="truck"
            description="Track stock shipped from Department of Health national storage to Lae AMS warehouse"
            :action-url="auth()->user()->hasRole('procurement_officer') ? getDashboardTransferRoute('create') : null"
            :action-label="auth()->user()->hasRole('procurement_officer') ? 'Request Shipment' : null"
        />

        <div class="module-panel p-6">
            <form action="{{ getDashboardTransferRoute('index') }}" method="GET" class="module-filter mb-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Transfer #, batch, or drug name..." class="input-field">
                    </div>
                    <div>
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="input-field">
                            <option value="">All Statuses</option>
                            @foreach((auth()->user()->hasRole('store_manager') ? ['sent', 'received', 'cancelled'] : ['pending', 'sent', 'received', 'cancelled']) as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ndohToLaeAmsTransferStatusLabel($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="form-label">Sent From</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="input-field">
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Search</button>
                    <a href="{{ getDashboardTransferRoute('index') }}" class="btn-module-secondary">Clear</a>
                </div>
            </form>

            @if($transfers->count() > 0)
                <div class="module-table-wrap overflow-x-auto">
                    <table class="module-table">
                        <thead>
                            <tr>
                                <th>Transfer #</th>
                                <th>Drug</th>
                                <th>Batch #</th>
                                <th>Qty Shipped</th>
                                <th>Date Shipped</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfers as $transfer)
                                <tr>
                                    <td class="whitespace-nowrap font-semibold text-ink dark:text-zinc-100">{{ $transfer->transfer_number }}</td>
                                    <td class="whitespace-nowrap">{{ $transfer->drug->drug_name ?? 'N/A' }}</td>
                                    <td class="whitespace-nowrap">{{ $transfer->batch_number }}</td>
                                    <td class="whitespace-nowrap">{{ number_format($transfer->quantity_sent) }}</td>
                                    <td class="whitespace-nowrap">{{ $transfer->formatSentDate() }}</td>
                                    <td class="whitespace-nowrap">
                                        <x-module.status-badge :variant="$transfer->status" :label="ndohToLaeAmsTransferStatusLabel($transfer->status)" />
                                    </td>
                                    <td class="whitespace-nowrap text-right">
                                        <div class="module-table-actions">
                                            <a href="{{ getDashboardTransferRoute('show', $transfer) }}" class="module-table-action">View</a>
                                        @if(canReceiveTransfers() && $transfer->canReceive())
                                            <a href="{{ getDashboardTransferRoute('show', $transfer) }}" class="module-table-action">Confirm</a>
                                        @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $transfers->appends(request()->except('page'))->links() }}</div>
            @else
                <div class="module-empty py-12">
                    <div class="module-empty-icon">
                        <x-dashboard.icon name="truck" class="h-6 w-6 text-muted" />
                    </div>
                    <p class="text-sm font-semibold text-ink dark:text-zinc-200">No shipments found</p>
                    <p class="mt-1 text-sm text-muted">Record a shipment when NDoH stock is dispatched to Lae AMS.</p>
                    @if(auth()->user()->hasRole('procurement_officer'))
                        <a href="{{ getDashboardTransferRoute('create') }}" class="btn-brand mt-4 text-xs uppercase tracking-wider">Record Shipment</a>
                    @endif
                </div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
