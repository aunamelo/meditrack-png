@php
    $statusClasses = [
        'pending' => 'bg-amber-100 text-amber-800',
        'approved' => 'bg-blue-100 text-blue-800',
        'rejected' => 'bg-red-100 text-red-800',
        'shipped' => 'bg-purple-100 text-purple-800',
        'received' => 'bg-green-100 text-green-800',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Hospital Supply</p>
            <h2 class="heading-page">{{ $hospitalOrder->order_number }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        @if(session('success'))<div class="mb-4 rounded-md border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-red-800">{{ session('error') }}</div>@endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="surface-panel lg:col-span-2">
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium">{{ $hospitalOrder->drug_name }} ({{ $hospitalOrder->dosage }})</h3>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$hospitalOrder->status] ?? 'bg-gray-100' }}">{{ hospitalOrderStatusLabel($hospitalOrder->status) }}</span>
                    </div>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                        <div><dt class="text-gray-500">Requested</dt><dd class="font-medium">{{ number_format($hospitalOrder->quantity_requested) }} units</dd></div>
                        @if($hospitalOrder->quantity_approved)<div><dt class="text-gray-500">Approved</dt><dd class="font-medium">{{ number_format($hospitalOrder->quantity_approved) }} units</dd></div>@endif
                        <div><dt class="text-gray-500">Requested by</dt><dd class="font-medium">{{ $hospitalOrder->requester->name ?? 'N/A' }}</dd></div>
                        @if($hospitalOrder->reviewer)<div><dt class="text-gray-500">Reviewed by</dt><dd class="font-medium">{{ $hospitalOrder->reviewer->name }} · {{ $hospitalOrder->reviewed_at?->format('M d, Y') }}</dd></div>@endif
                    </dl>
                    @if($hospitalOrder->notes)<p class="text-sm text-gray-600"><span class="font-medium">Notes:</span> {{ $hospitalOrder->notes }}</p>@endif
                    @if($hospitalOrder->rejection_reason)<p class="rounded-md bg-red-50 p-3 text-sm text-red-700"><span class="font-medium">Rejection reason:</span> {{ $hospitalOrder->rejection_reason }}</p>@endif
                    @if($hospitalOrder->stockTransfer)
                        <a href="{{ getDashboardHospitalShipmentRoute('show', $hospitalOrder->stockTransfer) }}" class="inline-flex text-sm font-semibold text-brand-600">View road delivery {{ $hospitalOrder->stockTransfer->transfer_number }} →</a>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                @if(auth()->user()->hasRole('store_manager') && $hospitalOrder->canApprove())
                    <div class="surface-panel p-6">
                        <h4 class="mb-4 font-semibold">Approve order</h4>
                        <form action="{{ getDashboardHospitalOrderRoute('approve', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="mb-1 block text-sm font-medium">Lae AMS batch *</label>
                                <select name="source_drug_id" required class="w-full rounded-md border-gray-300 text-sm">
                                    <option value="">Select available stock</option>
                                    @forelse($availableDrugs as $drug)
                                        <option value="{{ $drug->id }}">{{ $drug->drug_name }} · Batch {{ $drug->batch_number }} · {{ $drug->quantity_on_hand }} on hand</option>
                                    @empty
                                        <option value="" disabled>No matching Lae AMS stock found</option>
                                    @endforelse
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium">Quantity approved *</label>
                                <input type="number" name="quantity_approved" min="1" max="{{ $hospitalOrder->quantity_requested }}" value="{{ old('quantity_approved', $hospitalOrder->quantity_requested) }}" required class="w-full rounded-md border-gray-300 text-sm">
                            </div>
                            <button type="submit" class="w-full rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Approve</button>
                        </form>
                    </div>
                    <div class="surface-panel p-6">
                        <h4 class="mb-4 font-semibold text-red-700">Reject order</h4>
                        <form action="{{ getDashboardHospitalOrderRoute('reject', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="rejection_reason" rows="3" required placeholder="Reason (e.g. out of stock)" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                            <button type="submit" class="w-full rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white">Reject</button>
                        </form>
                    </div>
                @endif

                @if(auth()->user()->hasRole('store_manager') && $hospitalOrder->canShip())
                    <div class="surface-panel p-6">
                        <h4 class="mb-4 font-semibold">Dispatch to Modilon Hospital</h4>
                        <form action="{{ getDashboardHospitalOrderRoute('ship', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="notes" rows="2" placeholder="Road delivery notes (optional)" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                            <button type="submit" class="w-full rounded-md bg-purple-600 px-4 py-2 text-sm font-semibold text-white">Dispatch by road to hospital</button>
                        </form>
                    </div>
                @endif

                @if(auth()->user()->hasRole('pharmacy_manager') && $hospitalOrder->canReceive())
                    <div class="surface-panel p-6">
                        <h4 class="mb-4 font-semibold">Confirm receipt</h4>
                        <form action="{{ getDashboardHospitalOrderRoute('receive', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="notes" rows="2" placeholder="Receipt notes (optional)" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                            <button type="submit" class="w-full rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white">Confirm received</button>
                        </form>
                        <a href="{{ getDashboardDiscrepancyRoute('create') }}?hospital_order={{ $hospitalOrder->id }}" class="mt-3 block text-center text-sm font-semibold text-red-600">Report a discrepancy</a>
                    </div>
                @endif
            </div>
        </div>
    </x-page-container>
</x-app-layout>
