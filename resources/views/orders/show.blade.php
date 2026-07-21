@php
    $statusClasses = [
        'pending' => 'bg-gray-100 text-gray-800',
        'ordered' => 'bg-blue-100 text-blue-800',
        'shipped' => 'bg-purple-100 text-purple-800',
        'received' => 'bg-green-100 text-green-800',
        'partial' => 'bg-yellow-100 text-yellow-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
    $remainingQty = $order->quantity_ordered - ($order->quantity_received ?? 0);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Order {{ $order->order_number }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showReceiveModal: {{ old('_receive_modal') && $errors->any() ? 'true' : 'false' }} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-md p-4">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-md p-4">{{ session('error') }}</div>
            @endif

            <div class="flex items-center justify-between">
                <a href="{{ getDashboardOrderRoute('index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-[#0f766e]">
                    ← Back to Orders
                </a>
                <div class="flex gap-3">
                    @if(canManageOrders() && $order->status === 'pending' && $order->created_by === auth()->id())
                        <a href="{{ getDashboardOrderRoute('edit', $order) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 rounded-md text-xs font-semibold text-gray-700 uppercase hover:bg-gray-200">Edit</a>
                        <details class="inline-block">
                            <summary class="inline-flex items-center px-3 py-2 bg-red-100 rounded-md text-xs font-semibold text-red-700 uppercase hover:bg-red-200 cursor-pointer list-none">Cancel</summary>
                            <form action="{{ getDashboardOrderRoute('cancel', $order) }}" method="POST" class="absolute mt-2 p-4 bg-white border rounded-lg shadow-lg z-10 w-80">
                                @csrf
                                <label for="cancel_reason" class="block text-sm font-medium text-gray-700 mb-1">Cancellation Reason</label>
                                <textarea name="reason" id="cancel_reason" rows="3" required class="w-full rounded-md border-gray-300 text-sm"></textarea>
                                <button type="submit" class="mt-2 inline-flex items-center px-3 py-2 bg-red-600 rounded-md text-xs font-semibold text-white uppercase">Confirm Cancel</button>
                            </form>
                        </details>
                    @endif
                    @if(canApproveOrders() && $order->canApprove())
                        <form action="{{ getDashboardOrderRoute('approve', $order) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-blue-600 rounded-md text-xs font-semibold text-white uppercase hover:bg-blue-700">Approve</button>
                        </form>
                    @endif
                    @if(canApproveOrders() && $order->status === 'pending')
                        <details class="inline-block">
                            <summary class="inline-flex items-center px-3 py-2 bg-red-100 rounded-md text-xs font-semibold text-red-700 uppercase hover:bg-red-200 cursor-pointer list-none">Cancel</summary>
                            <form action="{{ getDashboardOrderRoute('cancel', $order) }}" method="POST" class="absolute mt-2 p-4 bg-white border rounded-lg shadow-lg z-10 w-80">
                                @csrf
                                <label for="admin_cancel_reason" class="block text-sm font-medium text-gray-700 mb-1">Cancellation Reason</label>
                                <textarea name="reason" id="admin_cancel_reason" rows="3" required class="w-full rounded-md border-gray-300 text-sm"></textarea>
                                <button type="submit" class="mt-2 inline-flex items-center px-3 py-2 bg-red-600 rounded-md text-xs font-semibold text-white uppercase">Confirm Cancel</button>
                            </form>
                        </details>
                    @endif
                    @if(canApproveOrders() && $order->canReceive())
                        <button type="button" @click="showReceiveModal = true" class="inline-flex items-center px-3 py-2 bg-[#0f766e] rounded-md text-xs font-semibold text-white uppercase hover:bg-[#0d5f59]">Receive</button>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
                    <p class="text-2xl font-bold text-[#0f766e] mb-3">{{ $order->order_number }}</p>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($order->status) }}</span>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-gray-500">Order Date</dt><dd class="font-medium text-gray-900">{{ $order->formatOrderDate() }}</dd></div>
                        <div><dt class="text-gray-500">Created By</dt><dd class="font-medium text-gray-900">{{ $order->creator->name ?? 'N/A' }}</dd></div>
                        <div>
                            <dt class="text-gray-500">Approval Status</dt>
                            <dd class="font-medium text-gray-900">
                                @if($order->approved_at)
                                    Approved on {{ $order->approved_at->format('M d, Y') }} by {{ $order->approver->name ?? 'N/A' }}
                                @else
                                    Pending approval
                                @endif
                            </dd>
                        </div>
                        @if($order->supplier_invoice)
                            <div><dt class="text-gray-500">Invoice #</dt><dd class="font-medium text-gray-900">{{ $order->supplier_invoice }}</dd></div>
                        @endif
                        @if($order->invoice_amount)
                            <div><dt class="text-gray-500">Invoice Amount</dt><dd class="font-medium text-gray-900">K {{ number_format($order->invoice_amount, 2) }}</dd></div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Drug & Delivery</h3>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Drug Name</dt>
                            <dd class="font-medium text-gray-900">
                                @if($order->drug)
                                    <a href="{{ getDashboardDrugRoute('show', $order->drug) }}" class="text-[#0f766e] hover:text-[#0d5f59]">{{ $order->drug->drug_name }}</a>
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>
                        <div><dt class="text-gray-500">Quantity Ordered</dt><dd class="font-medium text-gray-900">{{ number_format($order->quantity_ordered) }}</dd></div>
                        <div><dt class="text-gray-500">Supplier</dt><dd class="font-medium text-gray-900">{{ $order->supplier }}</dd></div>
                        <div><dt class="text-gray-500">Source</dt><dd class="font-medium text-gray-900">{{ ucfirst($order->source) }}</dd></div>
                        <div><dt class="text-gray-500">Expected Delivery</dt><dd class="font-medium text-gray-900">{{ $order->formatDeliveryDate() }}</dd></div>
                        @if($order->isOverdue())
                            <div><dt class="text-gray-500">Overdue</dt><dd class="font-medium text-red-600">{{ $order->daysOverdue() }} days</dd></div>
                        @elseif($order->expected_delivery_date && !in_array($order->status, ['received', 'cancelled']))
                            <div><dt class="text-gray-500">Days to Delivery</dt><dd class="font-medium text-gray-900">{{ now()->diffInDays($order->expected_delivery_date, false) }} days</dd></div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Receipt</h3>
                    <p class="text-3xl font-bold text-gray-900 mb-2">{{ number_format($order->quantity_received ?? 0) }}</p>
                    <p class="text-sm text-gray-500 mb-4">of {{ number_format($order->quantity_ordered) }} received</p>
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                        <div class="bg-[#0f766e] h-3 rounded-full" style="width: {{ $order->getProgressPercentage() }}%"></div>
                    </div>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Receipt Status</dt>
                            <dd class="font-medium text-gray-900">
                                @if($order->status === 'received')
                                    Complete
                                @elseif($order->status === 'partial')
                                    Partial delivery
                                @else
                                    Not yet received
                                @endif
                            </dd>
                        </div>
                        @if($order->actual_delivery_date)
                            <div><dt class="text-gray-500">Received Date</dt><dd class="font-medium text-gray-900">{{ $order->actual_delivery_date->format('M d, Y') }}</dd></div>
                        @endif
                        @if($order->receiver)
                            <div><dt class="text-gray-500">Received By</dt><dd class="font-medium text-gray-900">{{ $order->receiver->name }}</dd></div>
                        @endif
                    </dl>
                </div>
            </div>

            @if($order->notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $order->notes }}</p>
                </div>
            @endif

            @if(canApproveOrders())
                @include('orders.receive-modal', ['order' => $order, 'remainingQty' => $remainingQty])
            @endif
        </div>
    </div>
</x-app-layout>
