<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Hospital Supply</p>
            <h2 class="heading-page">{{ $hospitalOrder->order_number }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.back-link :href="getDashboardHospitalOrderRoute('index')" label="Back to Hospital Orders" class="mb-6" />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-module.detail-card title="{{ $hospitalOrder->drug_name }}" subtitle="{{ $hospitalOrder->dosage }}" class="lg:col-span-2">
                <div class="mb-4">
                    <x-module.status-badge :variant="$hospitalOrder->status" :label="hospitalOrderStatusLabel($hospitalOrder->status)" />
                </div>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-module.detail-field label="Requested" :value="number_format($hospitalOrder->quantity_requested) . ' units'" />
                    @if($hospitalOrder->quantity_approved)
                        <x-module.detail-field label="Approved" :value="number_format($hospitalOrder->quantity_approved) . ' units'" />
                    @endif
                    <x-module.detail-field label="Requested by" :value="$hospitalOrder->requester->name ?? 'N/A'" />
                    @if($hospitalOrder->reviewer)
                        <x-module.detail-field label="Reviewed by" :value="$hospitalOrder->reviewer->name . ' · ' . $hospitalOrder->reviewed_at?->format('M d, Y')" />
                    @endif
                </dl>
                @if($hospitalOrder->notes)
                    <p class="mt-4 text-sm text-ink-secondary dark:text-zinc-300"><span class="font-semibold">Notes:</span> {{ $hospitalOrder->notes }}</p>
                @endif
                @if($hospitalOrder->rejection_reason)
                    <p class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                        <span class="font-semibold">Rejection reason:</span> {{ $hospitalOrder->rejection_reason }}
                    </p>
                @endif
                @if($hospitalOrder->stockTransfer)
                    <a href="{{ getDashboardHospitalShipmentRoute('show', $hospitalOrder->stockTransfer) }}" class="module-table-link mt-4 inline-flex text-sm">
                        View road delivery {{ $hospitalOrder->stockTransfer->transfer_number }} →
                    </a>
                @endif
            </x-module.detail-card>

            <div class="space-y-6">
                @if(auth()->user()->hasRole('store_manager') && $hospitalOrder->canApprove())
                    <x-module.detail-card title="Approve order">
                        <form action="{{ getDashboardHospitalOrderRoute('approve', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="form-label">Lae AMS batch *</label>
                                <select name="source_drug_id" required class="input-field">
                                    <option value="">Select available stock</option>
                                    @forelse($availableDrugs as $drug)
                                        <option value="{{ $drug->id }}">{{ $drug->drug_name }} · Batch {{ $drug->batch_number }} · {{ $drug->quantity_on_hand }} on hand</option>
                                    @empty
                                        <option value="" disabled>No matching Lae AMS stock found</option>
                                    @endforelse
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Quantity approved *</label>
                                <input type="number" name="quantity_approved" min="1" max="{{ $hospitalOrder->quantity_requested }}" value="{{ old('quantity_approved', $hospitalOrder->quantity_requested) }}" required class="input-field">
                            </div>
                            <button type="submit" class="btn-brand w-full text-xs uppercase">Approve</button>
                        </form>
                    </x-module.detail-card>
                    <x-module.detail-card title="Reject order">
                        <form action="{{ getDashboardHospitalOrderRoute('reject', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="rejection_reason" rows="3" required placeholder="Reason (e.g. out of stock)" class="input-field"></textarea>
                            <button type="submit" class="w-full rounded-lg bg-rose-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-rose-700">Reject</button>
                        </form>
                    </x-module.detail-card>
                @endif

                @if(auth()->user()->hasRole('store_manager') && $hospitalOrder->canShip())
                    <x-module.detail-card title="Dispatch to Modilon Hospital">
                        <p class="mb-3 text-sm text-muted">This deducts stock from Lae AMS only. Modilon inventory is updated when the pharmacy manager confirms receipt.</p>
                        <form action="{{ getDashboardHospitalOrderRoute('ship', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="notes" rows="2" placeholder="Road delivery notes (optional)" class="input-field"></textarea>
                            <button type="submit" class="w-full rounded-lg bg-purple-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-purple-700">Dispatch by road to hospital</button>
                        </form>
                    </x-module.detail-card>
                @endif

                @if(auth()->user()->hasRole('pharmacy_manager') && $hospitalOrder->canReceive())
                    <x-module.detail-card title="Confirm receipt">
                        <p class="mb-3 text-sm text-muted">Confirming receipt will add {{ number_format($hospitalOrder->quantity_approved ?? $hospitalOrder->quantity_requested) }} units to Modilon Hospital inventory.</p>
                        <form action="{{ getDashboardHospitalOrderRoute('receive', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="notes" rows="2" placeholder="Receipt notes (optional)" class="input-field"></textarea>
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-emerald-700">Confirm received</button>
                        </form>
                        <a href="{{ getDashboardDiscrepancyRoute('create') }}?hospital_order={{ $hospitalOrder->id }}" class="mt-3 block text-center text-sm font-semibold text-rose-600 hover:text-rose-700">Report a discrepancy</a>
                    </x-module.detail-card>
                @endif
            </div>
        </div>
    </x-page-container>
</x-app-layout>
