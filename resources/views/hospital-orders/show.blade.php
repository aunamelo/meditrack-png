<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Supply</p>
            <h2 class="heading-page">{{ $hospitalOrder->order_number }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.back-link :href="getDashboardHospitalOrderRoute('index')" label="Back to Hospital Orders" class="mb-6" />

        @if(! in_array($hospitalOrder->status, ['cancelled'], true))
            <div class="module-panel mb-6 p-6">
                <x-service-pipeline
                    title="Hospital supply request"
                    subtitle="{{ $hospitalOrder->order_number }} · {{ $hospitalOrder->drug_name }} ({{ $hospitalOrder->dosage }})"
                    :status-label="hospitalOrderStatusLabel($hospitalOrder->status)"
                    :progress="$hospitalOrder->pipelineProgressPercentage()"
                    :stages="$hospitalOrder->pipelineStages()"
                />
            </div>
        @endif

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
                        <x-module.detail-field label="Reviewed by" :value="$hospitalOrder->reviewer->name . ' · ' . formatDate($hospitalOrder->reviewed_at)" />
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
                    @if($hospitalOrder->stockTransfer->expected_arrival_at)
                        <p class="mt-2 text-sm {{ $hospitalOrder->stockTransfer->isArrivalOverdue() ? 'font-semibold text-rose-700 dark:text-rose-300' : 'text-muted' }}">
                            ETA: {{ $hospitalOrder->stockTransfer->formatExpectedArrival() }}
                            @if($hospitalOrder->stockTransfer->isArrivalOverdue())
                                · Overdue
                            @endif
                        </p>
                    @endif
                    @if($hospitalOrder->stockTransfer->vehicle)
                        <p class="mt-2 text-sm text-muted">
                            Vehicle: <span class="font-medium text-ink dark:text-zinc-200">{{ $hospitalOrder->stockTransfer->vehicle->displayLabel() }}</span>
                        </p>
                    @endif
                @endif
            </x-module.detail-card>

            <div class="space-y-6">
                @if(auth()->user()->hasRole('store_manager') && $hospitalOrder->canApprove())
                    <x-module.detail-card title="Approve order">
                        <form action="{{ getDashboardHospitalOrderRoute('approve', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <x-form-label for="source_drug_id" required>Lae AMS batch</x-form-label>
                                <select name="source_drug_id" id="source_drug_id" required class="input-field">
                                    <option value="">Select available stock</option>
                                    @forelse($availableDrugs as $drug)
                                        <option value="{{ $drug->id }}">{{ $drug->drug_name }} · Batch {{ $drug->batch_number }} · {{ $drug->quantity_on_hand }} on hand</option>
                                    @empty
                                        <option value="" disabled>No matching Lae AMS stock found</option>
                                    @endforelse
                                </select>
                            </div>
                            <div>
                                <x-form-label for="quantity_approved" required>Quantity approved</x-form-label>
                                <input type="number" name="quantity_approved" id="quantity_approved" min="1" max="{{ $hospitalOrder->quantity_requested }}" value="{{ old('quantity_approved', $hospitalOrder->quantity_requested) }}" required class="input-field">
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
                        <p class="mb-3 text-sm text-muted">Assign a vehicle and dispatch. Stock is deducted from Lae AMS; Modilon inventory updates when the pharmacy confirms receipt.</p>
                        <form action="{{ getDashboardHospitalOrderRoute('ship', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <x-form-label for="vehicle_id" required>Assigned vehicle</x-form-label>
                                <select name="vehicle_id" id="vehicle_id" required class="input-field">
                                    <option value="">Select vehicle...</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>
                                            {{ $vehicle->displayLabel() }} · {{ $vehicle->typeLabel() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                <p class="mt-1 text-xs text-muted">Vehicle registration is stored for future delivery tracking.</p>
                            </div>
                            <div>
                                <x-form-label for="expected_arrival_at" required>Estimated arrival at Modilon</x-form-label>
                                <input
                                    type="datetime-local"
                                    name="expected_arrival_at"
                                    id="expected_arrival_at"
                                    required
                                    min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                                    value="{{ old('expected_arrival_at', now()->addDays(2)->setTime(17, 0)->format('Y-m-d\TH:i')) }}"
                                    class="input-field"
                                >
                                @error('expected_arrival_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                <p class="mt-1 text-xs text-muted">Shown on the live map and hospital shipment page so staff can track ETA.</p>
                            </div>
                            <div>
                                <x-form-label for="ship_notes" optional>Road delivery notes</x-form-label>
                                <textarea name="notes" id="ship_notes" rows="2" placeholder="Road delivery notes" class="input-field">{{ old('notes') }}</textarea>
                                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-brand-700">Dispatch by road to hospital</button>
                        </form>
                    </x-module.detail-card>
                @endif

                @if(auth()->user()->hasRole('pharmacy_manager') && $hospitalOrder->canReceive())
                    @php
                        $expectedQty = (int) ($hospitalOrder->quantity_approved ?? $hospitalOrder->quantity_requested);
                        $sourceBatch = $hospitalOrder->stockTransfer?->batch_number;
                        $sourceExpiry = $hospitalOrder->stockTransfer?->drug?->expiry_date;
                    @endphp
                    <x-module.detail-card title="Verify &amp; receive delivery">
                        <p class="mb-3 text-sm text-muted">
                            Physically check quantity, batch, and expiry against the Lae AMS dispatch before adding stock to Modilon inventory.
                        </p>
                        <dl class="mb-4 space-y-1 rounded-lg border border-line bg-canvas p-3 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-muted">Expected qty</dt><dd class="font-semibold tabular-nums">{{ number_format($expectedQty) }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-muted">Batch on dispatch</dt><dd class="font-semibold">{{ $sourceBatch ?: '—' }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-muted">Expiry on dispatch</dt><dd class="font-semibold">{{ $sourceExpiry ? formatDate($sourceExpiry) : '—' }}</dd></div>
                        </dl>
                        <form action="{{ getDashboardHospitalOrderRoute('receive', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <x-form-label for="quantity_received" required>Quantity received</x-form-label>
                                <input type="number" name="quantity_received" id="quantity_received" min="0" max="{{ $expectedQty }}" value="{{ old('quantity_received', $expectedQty) }}" required class="input-field">
                                @error('quantity_received')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <x-form-label for="condition" required>Delivery condition</x-form-label>
                                <select name="condition" id="condition" required class="input-field">
                                    @foreach([
                                        'good' => 'Good — matches dispatch',
                                        'short_shipment' => 'Short shipment',
                                        'damaged' => 'Damaged goods',
                                        'wrong_item' => 'Wrong item',
                                        'expired' => 'Expired product',
                                        'other' => 'Other issue',
                                    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('condition', 'good') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('condition')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <label class="flex items-start gap-2 text-sm text-ink">
                                <input type="checkbox" name="batch_verified" value="1" @checked(old('batch_verified')) required class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                                <span>I verified the batch number matches the dispatch paperwork.</span>
                            </label>
                            @error('batch_verified')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                            <label class="flex items-start gap-2 text-sm text-ink">
                                <input type="checkbox" name="expiry_verified" value="1" @checked(old('expiry_verified')) required class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                                <span>I verified the expiry date matches the dispatch paperwork.</span>
                            </label>
                            @error('expiry_verified')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                            <textarea name="notes" rows="2" placeholder="Receipt / verification notes (optional)" class="input-field">{{ old('notes') }}</textarea>
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-emerald-700">Verify &amp; confirm received</button>
                        </form>
                        <a href="{{ getDashboardDiscrepancyRoute('create') }}?hospital_order={{ $hospitalOrder->id }}" class="mt-3 block text-center text-sm font-semibold text-rose-600 hover:text-rose-700">Report a discrepancy separately</a>
                    </x-module.detail-card>
                @endif
            </div>
        </div>
    </x-page-container>
</x-app-layout>
