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
                <form action="{{ getDashboardTransferRoute('approve', $transfer) }}" method="POST" class="inline" onsubmit="return confirm('Approve this shipment? NDoH stock will be deducted and Lae AMS inventory will be created.');">
                    @csrf
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Approve &amp; send</button>
                </form>
            @endif
            @if(canReceiveTransfers() && $transfer->canReceive())
                <form action="{{ getDashboardTransferRoute('receive', $transfer) }}" method="POST" class="inline-flex flex-wrap items-center gap-3">
                    @csrf
                    <input type="text" name="notes" placeholder="Optional receipt note..." class="input-field w-64 text-sm">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Confirm Receipt</button>
                </form>
            @endif
        </div>

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
                        <x-module.detail-field label="Approved At" :value="$transfer->approved_at?->format('M d, Y g:i A') ?? 'N/A'" />
                    @elseif($transfer->status === 'pending')
                        <x-module.detail-field label="Approval" value="Awaiting NDoH Admin" />
                    @endif
                    @if($transfer->receiver)
                        <x-module.detail-field label="Received By" :value="$transfer->receiver->name" />
                        <x-module.detail-field label="Received At" :value="$transfer->received_at?->format('M d, Y g:i A') ?? 'N/A'" />
                    @endif
                    <x-module.detail-field label="Route" value="NDoH → Lae AMS" />
                    <x-module.detail-field label="Logistics" value="National shipment to regional warehouse" />
                </dl>
            </x-module.detail-card>

            <x-module.detail-card title="Drug & Batch">
                <dl class="space-y-4">
                    <x-module.detail-field label="Drug Name">
                        {{ $transfer->drug->drug_name ?? 'N/A' }}@if($transfer->drug) ({{ $transfer->drug->dosage }})@endif
                    </x-module.detail-field>
                    <x-module.detail-field label="Source Batch #" :value="$transfer->batch_number" />
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Quantity Shipped</dt>
                        <dd class="mt-1 font-display text-xl font-bold text-ink dark:text-zinc-100">{{ number_format($transfer->quantity_sent) }}</dd>
                    </div>
                    @if($transfer->destinationDrug)
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
                    @elseif($transfer->destinationDrug)
                        <x-module.detail-field label="Lae AMS Inventory">
                            @if(auth()->user()->hasRole('store_manager'))
                                <a href="{{ getDashboardDrugRoute('show', $transfer->destinationDrug) }}" class="module-table-link">
                                    View batch ({{ number_format($transfer->destinationDrug->quantity_on_hand) }} on hand)
                                </a>
                            @else
                                Batch {{ $transfer->destinationDrug->batch_number }} — {{ number_format($transfer->destinationDrug->quantity_on_hand) }} units
                            @endif
                        </x-module.detail-field>
                    @endif
                </dl>
            </x-module.detail-card>
        </div>

        @if($transfer->notes)
            <x-module.detail-card title="Notes" class="mt-6">
                <p class="whitespace-pre-line text-sm text-ink-secondary dark:text-zinc-300">{{ $transfer->notes }}</p>
            </x-module.detail-card>
        @endif
    </x-page-container>
</x-app-layout>
