<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Logistics</p>
            <h2 class="heading-page">Shipment {{ $transfer->transfer_number }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <div class="module-actions-bar">
            <x-module.back-link :href="getDashboardTransferRoute('index')" label="Back to Shipments" />
            @if(canApproveTransfers() && $transfer->canApprove())
                <form
                    action="{{ getDashboardTransferRoute('approve', $transfer) }}"
                    method="POST"
                    class="inline"
                    data-confirm="Approve this combined delivery? NDoH stock for all batches will be deducted and the shipment will be marked in transit to Lae AMS."
                    data-confirm-title="Approve shipment"
                    data-confirm-label="Approve & send"
                >
                    @csrf
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Approve &amp; send</button>
                </form>
            @endif
            @if(canReceiveTransfers() && $transfer->canReceive())
                <form
                    action="{{ getDashboardTransferRoute('receive', $transfer) }}"
                    method="POST"
                    class="inline-flex flex-wrap items-center gap-3"
                    data-confirm="Confirm receipt? This will add all batches on this delivery to Lae AMS inventory."
                    data-confirm-title="Confirm receipt"
                    data-confirm-label="Confirm receipt"
                >
                    @csrf
                    <input type="text" name="notes" placeholder="Optional receipt note..." class="input-field w-64 text-sm">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Confirm Receipt</button>
                </form>
            @endif
        </div>

        @if($transfer->to_level === 'lae_ams' && $transfer->hospital_order_id === null && $transfer->status !== 'cancelled')
            <div class="module-panel mb-6 p-6">
                <x-service-pipeline
                    title="NDoH → Lae AMS shipment"
                    subtitle="{{ $transfer->transfer_number }} · {{ $transfer->medicinesLabel() }}"
                    :status-label="ndohToLaeAmsTransferStatusLabel($transfer->status)"
                    :progress="$transfer->pipelineProgressPercentage()"
                    :stages="$transfer->pipelineStages()"
                />
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-module.detail-card title="Shipment Details">
                <p class="font-display text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $transfer->transfer_number }}</p>
                <div class="mt-3">
                    <x-module.status-badge :variant="$transfer->status" :label="ndohToLaeAmsTransferStatusLabel($transfer->status)" />
                </div>
                <dl class="mt-4 space-y-4">
                    <x-module.detail-field :label="$transfer->status === 'pending' ? 'Requested ship date' : 'Date Shipped'" :value="$transfer->formatSentDate()" />
                    <x-module.detail-field label="Requested By" :value="$transfer->sender->name ?? 'N/A'" />
                    @if($transfer->approver)
                        <x-module.detail-field label="Approved By" :value="$transfer->approver->name" />
                        <x-module.detail-field label="Approved At" :value="formatDateTime($transfer->approved_at)" />
                    @elseif($transfer->status === 'pending')
                        <x-module.detail-field label="Approval" value="Awaiting NDoH Admin" />
                    @endif
                    @if($transfer->receiver)
                        <x-module.detail-field label="Received By" :value="$transfer->receiver->name" />
                        <x-module.detail-field label="Received At" :value="formatDateTime($transfer->received_at)" />
                    @endif
                    <x-module.detail-field label="Route" value="NDoH → Lae AMS" />
                    <x-module.detail-field label="Logistics" value="National shipment to regional warehouse" />
                    <x-module.detail-field label="Batches on delivery" :value="(string) $transfer->lineCount()" />
                </dl>
            </x-module.detail-card>

            <x-module.detail-card title="Delivery summary">
                <dl class="space-y-4">
                    <x-module.detail-field label="Medicines" :value="$transfer->medicinesLabel()" />
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Total quantity</dt>
                        <dd class="mt-1 font-display text-xl font-bold text-ink dark:text-zinc-100">{{ number_format($transfer->quantity_sent) }}</dd>
                    </div>
                    @if($transfer->items->isEmpty() && $transfer->destinationDrug)
                        <x-module.detail-field label="Lae AMS Batch #" :value="$transfer->destinationDrug->batch_number" />
                    @endif
                </dl>
            </x-module.detail-card>

            <x-module.detail-card title="Inventory Impact">
                <dl class="space-y-4">
                    <x-module.detail-field label="From" value="NDoH National Storage" />
                    <x-module.detail-field label="To" value="Lae AMS Warehouse" />
                    @if($transfer->status === 'pending')
                        <x-module.detail-field label="Stock movement" value="Held until NDoH Admin approves" />
                    @elseif($transfer->status === 'sent')
                        <x-module.detail-field label="Stock movement" value="Deducted from NDoH — awaiting Store Manager receipt at Lae AMS" />
                    @elseif($transfer->status === 'received')
                        <x-module.detail-field label="Stock movement" value="Received into Lae AMS inventory" />
                    @endif
                </dl>
            </x-module.detail-card>
        </div>

        <x-module.detail-card title="Batches on this delivery" class="mt-6">
            @php
                $lines = $transfer->items->isNotEmpty()
                    ? $transfer->items
                    : collect([(object) [
                        'drug' => $transfer->drug,
                        'batch_number' => $transfer->batch_number,
                        'quantity_sent' => $transfer->quantity_sent,
                        'destinationDrug' => $transfer->destinationDrug,
                    ]]);
            @endphp
            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Drug</th>
                            <th>Source batch</th>
                            <th>Qty</th>
                            <th>Lae AMS batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $line)
                            <tr>
                                <td>
                                    {{ $line->drug->drug_name ?? 'N/A' }}
                                    @if($line->drug)
                                        <span class="text-muted">({{ $line->drug->dosage }})</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">{{ $line->batch_number }}</td>
                                <td class="whitespace-nowrap">{{ number_format($line->quantity_sent) }}</td>
                                <td class="whitespace-nowrap">
                                    @if($line->destinationDrug ?? null)
                                        @if(auth()->user()->hasRole('store_manager'))
                                            <a href="{{ getDashboardDrugRoute('show', $line->destinationDrug) }}" class="module-table-link">
                                                {{ $line->destinationDrug->batch_number }}
                                            </a>
                                        @else
                                            {{ $line->destinationDrug->batch_number }}
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-module.detail-card>

        @if($transfer->notes)
            <x-module.detail-card title="Notes" class="mt-6">
                <p class="whitespace-pre-line text-sm text-ink-secondary dark:text-zinc-300">{{ $transfer->notes }}</p>
            </x-module.detail-card>
        @endif
    </x-page-container>
</x-app-layout>
