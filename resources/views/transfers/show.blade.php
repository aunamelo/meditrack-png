@php
    $statusClasses = [
        'sent' => 'bg-blue-100 text-blue-800',
        'received' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Shipment {{ $transfer->transfer_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-md p-4">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-md p-4">{{ session('error') }}</div>
            @endif

            <div class="flex items-center justify-between">
                <a href="{{ getDashboardTransferRoute('index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-[#0f766e]">← Back to Shipments</a>
                @if(canReceiveTransfers() && $transfer->canReceive())
                    <form action="{{ getDashboardTransferRoute('receive', $transfer) }}" method="POST" class="inline-flex items-center gap-3">
                        @csrf
                        <input type="text" name="notes" placeholder="Optional receipt note..." class="rounded-md border-gray-300 text-sm shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0f766e] rounded-md text-xs font-semibold text-white uppercase hover:bg-[#0d5f59]">Confirm Receipt</button>
                    </form>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipment Details</h3>
                    <p class="text-2xl font-bold text-[#0f766e] mb-2">{{ $transfer->transfer_number }}</p>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$transfer->status] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($transfer->status) }}</span>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-gray-500">Date Sent</dt><dd class="font-medium">{{ $transfer->formatSentDate() }}</dd></div>
                        <div><dt class="text-gray-500">Sent By</dt><dd class="font-medium">{{ $transfer->sender->name ?? 'N/A' }}</dd></div>
                        @if($transfer->receiver)
                            <div><dt class="text-gray-500">Received By</dt><dd class="font-medium">{{ $transfer->receiver->name }}</dd></div>
                            <div><dt class="text-gray-500">Received At</dt><dd class="font-medium">{{ $transfer->received_at?->format('M d, Y g:i A') ?? 'N/A' }}</dd></div>
                        @endif
                        <div><dt class="text-gray-500">Route</dt><dd class="font-medium">NDoH → Lae AMS</dd></div>
                    </dl>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Drug & Batch</h3>
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-gray-500">Drug Name</dt><dd class="font-medium">{{ $transfer->drug->drug_name ?? 'N/A' }} @if($transfer->drug) ({{ $transfer->drug->dosage }}) @endif</dd></div>
                        <div><dt class="text-gray-500">Source Batch #</dt><dd class="font-medium">{{ $transfer->batch_number }}</dd></div>
                        <div><dt class="text-gray-500">Quantity Sent</dt><dd class="font-medium text-lg">{{ number_format($transfer->quantity_sent) }}</dd></div>
                        @if($transfer->destinationDrug)
                            <div><dt class="text-gray-500">Lae AMS Batch #</dt><dd class="font-medium">{{ $transfer->destinationDrug->batch_number }}</dd></div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory Impact</h3>
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-gray-500">From</dt><dd class="font-medium">NDoH National Storage</dd></div>
                        <div><dt class="text-gray-500">To</dt><dd class="font-medium">Lae AMS Warehouse</dd></div>
                        @if($transfer->destinationDrug)
                            <div>
                                <dt class="text-gray-500">Lae AMS Inventory</dt>
                                <dd class="font-medium">
                                    @if(auth()->user()->hasRole('store_manager'))
                                        <a href="{{ getDashboardDrugRoute('show', $transfer->destinationDrug) }}" class="text-[#0f766e] hover:text-[#0d5f59]">View batch ({{ number_format($transfer->destinationDrug->quantity_on_hand) }} on hand)</a>
                                    @else
                                        Batch {{ $transfer->destinationDrug->batch_number }} — {{ number_format($transfer->destinationDrug->quantity_on_hand) }} units
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            @if($transfer->notes)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $transfer->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
