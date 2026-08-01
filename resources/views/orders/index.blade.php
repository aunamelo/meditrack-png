<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Supply chain</p>
            <h2 class="heading-page">Procurement Orders</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="clipboard"
            description="Monitor orders from registered India & China manufacturers (NDoH import policy)"
            :action-url="canManageOrders() ? getDashboardOrderRoute('create') : null"
            :action-label="canManageOrders() ? 'New Order' : null"
        />

        <div class="module-panel p-6">
            <form action="{{ getDashboardOrderRoute('index') }}" method="GET" class="module-filter mb-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Order #, drug name, or supplier..." class="input-field">
                    </div>
                    <div>
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="input-field">
                            <option value="">All Statuses</option>
                            @foreach(['pending', 'manufacturing', 'shipped', 'customs', 'fx_cleared', 'received', 'partial', 'cancelled'] as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ \App\Models\Order::make(['status' => $status])->statusLabel() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="input-field">
                    </div>
                    <div>
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="input-field">
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Search</button>
                    <a href="{{ getDashboardOrderRoute('index') }}" class="btn-module-secondary">Clear</a>
                </div>
            </form>

            @if($orders->count() > 0)
                <div class="module-table-wrap overflow-x-auto">
                    <table class="module-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Drug</th>
                                <th>Qty</th>
                                <th>Supplier</th>
                                <th>Order Date</th>
                                <th>Expected</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td class="whitespace-nowrap font-semibold text-ink dark:text-zinc-100">{{ $order->order_number }}</td>
                                    <td>
                                        {{ $order->itemsSummary() }}
                                        @if($order->hasMultipleItems())
                                            <span class="block text-xs text-muted">{{ number_format($order->quantity_ordered) }} units total</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap">{{ number_format($order->quantity_ordered) }}</td>
                                    <td class="whitespace-nowrap">
                                        @if($order->registeredSupplier)
                                            <span class="font-medium text-ink dark:text-zinc-100">{{ $order->registeredSupplier->name }}</span>
                                            <span class="block text-xs text-muted">{{ $order->registeredSupplier->countryLabel() }}</span>
                                        @else
                                            {{ $order->getAttribute('supplier') ?? '—' }}
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap">{{ $order->formatOrderDate() }}</td>
                                    <td class="whitespace-nowrap">
                                        {{ $order->formatDeliveryDate() }}
                                        @if($order->isOverdue())
                                            <span class="block text-xs text-rose-600">{{ $order->daysOverdue() }} days overdue</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <x-module.status-badge :variant="$order->status" :label="$order->statusLabel()" />
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <div class="module-progress-track">
                                            <div class="module-progress-bar" style="width: {{ $order->getProgressPercentage() }}%"></div>
                                        </div>
                                        <span class="text-xs text-muted">{{ $order->getProgressPercentage() }}%</span>
                                    </td>
                                    <td class="whitespace-nowrap text-right">
                                        <div class="module-table-actions">
                                            <a href="{{ getDashboardOrderRoute('show', $order) }}" class="module-table-action">View</a>
                                            @if(canManageOrders() && $order->status === 'pending' && $order->created_by === auth()->id())
                                                <a href="{{ getDashboardOrderRoute('edit', $order) }}" class="module-table-action module-table-action-edit">Edit</a>
                                            @endif
                                        @if(canApproveOrders() && $order->canApprove())
                                            <form action="{{ getDashboardOrderRoute('approve', $order) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-sm font-medium text-blue-600 hover:text-blue-700">Approve</button>
                                            </form>
                                        @endif
                                        @if(canApproveOrders() && $order->status === 'pending')
                                            <form action="{{ getDashboardOrderRoute('destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('Delete this order?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-700">Delete</button>
                                            </form>
                                        @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $orders->appends(request()->except('page'))->links() }}</div>
            @else
                <div class="module-empty py-12">
                    <div class="module-empty-icon">
                        <x-dashboard.icon name="clipboard" class="h-6 w-6 text-muted" />
                    </div>
                    <p class="text-sm font-semibold text-ink dark:text-zinc-200">No orders found</p>
                    <p class="mt-1 text-sm text-muted">Get started by creating a new procurement order.</p>
                    @if(canManageOrders())
                        <a href="{{ getDashboardOrderRoute('create') }}" class="btn-brand mt-4 text-xs uppercase tracking-wider">New Order</a>
                    @endif
                </div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
