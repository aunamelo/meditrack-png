<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Supply</p>
            <h2 class="heading-page">Hospital Orders</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="hospital"
            :description="auth()->user()->hasRole('store_manager')
                ? 'Review, approve, reject, and dispatch orders by road based on Lae AMS availability.'
                : 'Submit replenishment requests to the regional warehouse.'"
            :action-url="auth()->user()->hasRole('pharmacy_manager') ? getDashboardHospitalOrderRoute('create') : null"
            :action-label="auth()->user()->hasRole('pharmacy_manager') ? 'New Request' : null"
        />

        <div class="module-panel p-6">
            <form method="GET" class="module-filter mb-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Order # or drug name..." class="input-field">
                    </div>
                    <div>
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="input-field">
                            <option value="">All statuses</option>
                            @foreach(['pending','approved','rejected','shipped','received'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ hospitalOrderStatusLabel($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Filter</button>
                    <a href="{{ getDashboardHospitalOrderRoute('index') }}" class="btn-module-secondary">Clear</a>
                </div>
            </form>

            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Drug</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="font-semibold text-ink dark:text-zinc-100">{{ $order->order_number }}</td>
                                <td>{{ $order->drug_name }} ({{ $order->dosage }})</td>
                                <td>{{ number_format($order->quantity_requested) }}</td>
                                <td>
                                    <x-module.status-badge :variant="$order->status" :label="hospitalOrderStatusLabel($order->status)" />
                                </td>
                                <td class="text-right">
                                    <div class="module-table-actions">
                                        <a href="{{ getDashboardHospitalOrderRoute('show', $order) }}" class="module-table-action">View</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-module.empty-row
                                :colspan="5"
                                title="No hospital orders found"
                                description="Orders will appear once requests are submitted or received."
                                :action-url="auth()->user()->hasRole('pharmacy_manager') ? getDashboardHospitalOrderRoute('create') : null"
                                :action-label="auth()->user()->hasRole('pharmacy_manager') ? 'New Request' : null"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="mt-6">{{ $orders->links() }}</div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
