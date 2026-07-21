@php
    $statusClasses = [
        'sent' => 'bg-blue-100 text-blue-800',
        'received' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Logistics</p>
            <h2 class="heading-page">Lae AMS Shipments</h2>
        </div>
    </x-slot>

    <x-page-container>
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-md p-4">{{ session('success') }}</div>
            @endif

            <div class="surface-panel">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">NDoH → Lae AMS shipments</h3>
                            <p class="mt-1 text-sm text-gray-500">Track drugs sent from national storage to Lae AMS</p>
                        </div>
                        @if(auth()->user()->hasRole('procurement_officer'))
                            <a href="{{ getDashboardTransferRoute('create') }}" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59]">
                                Record Shipment
                            </a>
                        @endif
                    </div>

                    <form action="{{ getDashboardTransferRoute('index') }}" method="GET" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Transfer #, batch, or drug name..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                    <option value="">All Statuses</option>
                                    @foreach(['sent', 'received', 'cancelled'] as $status)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Sent From</label>
                                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0f766e] text-white text-xs font-semibold uppercase rounded-md hover:bg-[#0d5f59]">Search</button>
                            <a href="{{ getDashboardTransferRoute('index') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold uppercase rounded-md hover:bg-gray-200">Clear</a>
                        </div>
                    </form>

                    @if($transfers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transfer #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Drug</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Sent</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Sent</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($transfers as $transfer)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $transfer->transfer_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transfer->drug->drug_name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transfer->batch_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($transfer->quantity_sent) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transfer->formatSentDate() }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$transfer->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst($transfer->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                <a href="{{ getDashboardTransferRoute('show', $transfer) }}" class="text-[#0f766e] hover:text-[#0d5f59]">View</a>
                                                @if(canReceiveTransfers() && $transfer->canReceive())
                                                    <span class="text-gray-300 mx-1">|</span>
                                                    <a href="{{ getDashboardTransferRoute('show', $transfer) }}" class="text-blue-600 hover:text-blue-800 font-medium">Confirm</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">{{ $transfers->appends(request()->except('page'))->links() }}</div>
                    @else
                        <div class="text-center py-12">
                            <h3 class="text-sm font-medium text-gray-900">No shipments found</h3>
                            <p class="mt-1 text-sm text-gray-500">Record a shipment when drugs are sent to Lae AMS.</p>
                            @if(auth()->user()->hasRole('procurement_officer'))
                                <div class="mt-6">
                                    <a href="{{ getDashboardTransferRoute('create') }}" class="inline-flex items-center px-4 py-2 bg-[#0f766e] text-white text-xs font-semibold uppercase rounded-md hover:bg-[#0d5f59]">Record Shipment</a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
    </x-page-container>
</x-app-layout>
