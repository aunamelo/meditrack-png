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
                    subtitle="{{ $hospitalOrder->order_number }} · {{ $hospitalOrder->medicinesLabel() }}"
                    :status-label="hospitalOrderStatusLabel($hospitalOrder->status)"
                    :progress="$hospitalOrder->pipelineProgressPercentage()"
                    :stages="$hospitalOrder->pipelineStages()"
                />
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-module.detail-card title="{{ $hospitalOrder->medicinesLabel() }}" subtitle="{{ $hospitalOrder->items->count() }} medicine line(s) · one road delivery when shipped" class="lg:col-span-2">
                <div class="mb-4">
                    <x-module.status-badge :variant="$hospitalOrder->status" :label="hospitalOrderStatusLabel($hospitalOrder->status)" />
                </div>

                <div class="mb-4 overflow-x-auto">
                    <table class="module-table text-sm">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Requested</th>
                                <th>Approved</th>
                                <th>Lae AMS batch</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hospitalOrder->items as $item)
                                <tr>
                                    <td class="font-medium">{{ $item->displayLabel() }}</td>
                                    <td class="tabular-nums">{{ number_format($item->quantity_requested) }}</td>
                                    <td class="tabular-nums">{{ $item->quantity_approved !== null ? number_format($item->quantity_approved) : '—' }}</td>
                                    <td>
                                        @if($item->sourceDrug)
                                            Batch {{ $item->sourceDrug->batch_number }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-module.detail-field label="Total requested" :value="number_format($hospitalOrder->totalQuantityRequested()) . ' units'" />
                    @if($hospitalOrder->totalQuantityApproved() > 0)
                        <x-module.detail-field label="Total approved" :value="number_format($hospitalOrder->totalQuantityApproved()) . ' units'" />
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
                        <p class="mb-3 text-sm text-muted">Assign a Lae AMS batch for every medicine (FEFO: earliest expiry listed first). All lines ship together in one vehicle.</p>
                        <form action="{{ getDashboardHospitalOrderRoute('approve', $hospitalOrder) }}" method="POST" class="space-y-4">
                            @csrf
                            @foreach($hospitalOrder->items as $index => $item)
                                @php
                                    $options = $availableDrugsByItem[$item->id] ?? collect();
                                    $defaultBatchId = old("items.$index.source_drug_id", $options->first()?->id);
                                @endphp
                                <div class="rounded-lg border border-line p-3">
                                    <p class="mb-2 text-sm font-semibold text-ink">{{ $item->displayLabel() }}</p>
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                    <div class="space-y-2">
                                        <div>
                                            <x-form-label :for="'source_drug_'.$item->id" required>Lae AMS batch (FEFO)</x-form-label>
                                            <select name="items[{{ $index }}][source_drug_id]" id="source_drug_{{ $item->id }}" required class="input-field">
                                                <option value="">Select available stock</option>
                                                @forelse($options as $drug)
                                                    <option value="{{ $drug->id }}" @selected((string) $defaultBatchId === (string) $drug->id)>
                                                        Batch {{ $drug->batch_number }}
                                                        · Exp {{ optional($drug->expiry_date)->format('d M Y') ?? '—' }}
                                                        · {{ number_format($drug->quantity_on_hand) }} on hand
                                                        @if($loop->first) · FEFO @endif
                                                    </option>
                                                @empty
                                                    <option value="" disabled>No matching Lae AMS stock found</option>
                                                @endforelse
                                            </select>
                                            @error("items.$index.source_drug_id")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <x-form-label :for="'qty_approved_'.$item->id" required>Quantity approved</x-form-label>
                                            <input
                                                type="number"
                                                name="items[{{ $index }}][quantity_approved]"
                                                id="qty_approved_{{ $item->id }}"
                                                min="1"
                                                max="{{ $item->quantity_requested }}"
                                                value="{{ old("items.$index.quantity_approved", $item->quantity_requested) }}"
                                                required
                                                class="input-field"
                                            >
                                            @error("items.$index.quantity_approved")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @error('items')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            <button type="submit" class="btn-brand w-full text-xs uppercase">Approve all lines</button>
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

                @if(auth()->user()->hasRole('store_manager') && in_array($hospitalOrder->status, ['approved', 'shipped', 'received'], true))
                    <x-module.detail-card title="Warehouse pick list">
                        <p class="mb-3 text-sm text-muted">
                            Print a FEFO pick sheet for the warehouse floor before (or after) dispatch.
                        </p>
                        <a
                            href="{{ getDashboardHospitalOrderRoute('pick-list', $hospitalOrder) }}"
                            target="_blank"
                            rel="noopener"
                            class="btn-module-secondary inline-flex w-full justify-center text-xs uppercase tracking-wider"
                        >
                            Print pick list
                        </a>
                    </x-module.detail-card>
                @endif

                @if(auth()->user()->hasRole('store_manager') && $hospitalOrder->canShip())
                    <x-module.detail-card title="Dispatch to Modilon Hospital">
                        <p class="mb-3 text-sm text-muted">
                            One vehicle carries all {{ $hospitalOrder->items->count() }} medicine(s) as a single road delivery.
                            Lae AMS stock is deducted now; Modilon inventory updates when pharmacy confirms receipt.
                        </p>
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
                            </div>
                            <div>
                                <x-form-label for="ship_notes" optional>Road delivery notes</x-form-label>
                                <textarea name="notes" id="ship_notes" rows="2" placeholder="Road delivery notes" class="input-field">{{ old('notes') }}</textarea>
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-brand-700">Dispatch all medicines by road</button>
                        </form>
                    </x-module.detail-card>
                @endif

                @if(auth()->user()->hasRole('pharmacy_manager') && $hospitalOrder->canReceive())
                    @php
                        $transferLines = $hospitalOrder->stockTransfer?->items ?? collect();
                    @endphp
                    <x-module.detail-card title="Verify &amp; receive delivery">
                        <p class="mb-3 text-sm text-muted">
                            Check each medicine on this single delivery against the Lae AMS dispatch, then confirm receipt.
                        </p>
                        <form action="{{ getDashboardHospitalOrderRoute('receive', $hospitalOrder) }}" method="POST" class="space-y-3">
                            @csrf
                            @forelse($transferLines as $index => $line)
                                <div class="rounded-lg border border-line bg-canvas p-3 text-sm">
                                    <p class="font-semibold text-ink">{{ $line->drug?->drug_name ?? 'Medicine' }}</p>
                                    <p class="text-xs text-muted">Batch {{ $line->batch_number }} · Exp {{ $line->drug?->expiry_date ? formatDate($line->drug->expiry_date) : '—' }}</p>
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $line->id }}">
                                    <div class="mt-2">
                                        <x-form-label :for="'qty_recv_'.$line->id" required>Quantity received (expected {{ number_format($line->quantity_sent) }})</x-form-label>
                                        <input
                                            type="number"
                                            name="items[{{ $index }}][quantity_received]"
                                            id="qty_recv_{{ $line->id }}"
                                            min="0"
                                            max="{{ $line->quantity_sent }}"
                                            value="{{ old("items.$index.quantity_received", $line->quantity_sent) }}"
                                            required
                                            class="input-field"
                                        >
                                        @error("items.$index.quantity_received")<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            @empty
                                {{-- Legacy single-drug transfer without item rows --}}
                                <input type="hidden" name="items[0][id]" value="0">
                                <div>
                                    <x-form-label for="quantity_received" required>Quantity received</x-form-label>
                                    <input type="number" name="items[0][quantity_received]" id="quantity_received" min="0" value="{{ old('items.0.quantity_received', $hospitalOrder->stockTransfer?->quantity_sent) }}" required class="input-field">
                                </div>
                            @endforelse

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
                                <span>I verified batch numbers match the dispatch paperwork.</span>
                            </label>
                            <label class="flex items-start gap-2 text-sm text-ink">
                                <input type="checkbox" name="expiry_verified" value="1" @checked(old('expiry_verified')) required class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                                <span>I verified expiry dates match the dispatch paperwork.</span>
                            </label>
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
