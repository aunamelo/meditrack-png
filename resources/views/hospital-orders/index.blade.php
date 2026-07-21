@php
    $statusClasses = [
        'pending' => 'bg-amber-100 text-amber-800',
        'approved' => 'bg-blue-100 text-blue-800',
        'rejected' => 'bg-red-100 text-red-800',
        'shipped' => 'bg-purple-100 text-purple-800',
        'received' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-gray-100 text-gray-800',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Hospital Supply</p>
            <h2 class="heading-page">Hospital Orders</h2>
        </div>
    </x-slot>

    <x-page-container>
        @if(session('success'))
            <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
        @endif

        <div class="surface-panel">
            <div class="p-6">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-zinc-100">
                            @if(auth()->user()->hasRole('store_manager'))
                                Modilon Hospital stock requests
                            @else
                                Request stock from Lae AMS
                            @endif
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                            @if(auth()->user()->hasRole('store_manager'))
                                Review, approve, reject, and dispatch orders by road based on Lae AMS availability.
                            @else
                                Submit replenishment requests to the regional warehouse.
                            @endif
                        </p>
                    </div>
                    @if(auth()->user()->hasRole('pharmacy_manager'))
                        <a href="{{ getDashboardHospitalOrderRoute('create') }}" class="mt-4 inline-flex items-center rounded-md bg-brand-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-brand-700 sm:mt-0">
                            New request
                        </a>
                    @endif
                </div>

                <form method="GET" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Order # or drug name..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring-brand-600">
                    </div>
                    <div>
                        <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring-brand-600">
                            <option value="">All statuses</option>
                            @foreach(['pending','approved','rejected','shipped','received'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ hospitalOrderStatusLabel($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                        <thead class="bg-gray-50 dark:bg-zinc-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Order #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Drug</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-zinc-800">
                            @forelse($orders as $order)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium">{{ $order->order_number }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $order->drug_name }} ({{ $order->dosage }})</td>
                                    <td class="px-4 py-3 text-sm">{{ number_format($order->quantity_requested) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">{{ hospitalOrderStatusLabel($order->status) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <a href="{{ getDashboardHospitalOrderRoute('show', $order) }}" class="font-semibold text-brand-600 hover:text-brand-700">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No hospital orders found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="mt-6">{{ $orders->links() }}</div>
                @endif
            </div>
        </div>
    </x-page-container>
</x-app-layout>
