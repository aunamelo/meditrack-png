<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Logistics</p>
            <h2 class="heading-page">{{ $transfer->transfer_number }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardHospitalShipmentRoute('index')" label="Back to road deliveries" class="mb-6" />

        <x-module.detail-card title="Road delivery details">
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-module.detail-field label="Drug">
                    {{ $transfer->drug->drug_name ?? 'N/A' }} ({{ $transfer->drug->dosage ?? '' }})
                </x-module.detail-field>
                <x-module.detail-field label="Quantity sent" :value="number_format($transfer->quantity_sent)" />
                <x-module.detail-field label="Batch" :value="$transfer->batch_number" />
                <x-module.detail-field label="Route" value="Lae AMS → Modilon Hospital (by road)" />
                <x-module.detail-field label="Assigned vehicle">
                    @if($transfer->vehicle)
                        <span class="font-medium text-ink dark:text-zinc-200">{{ $transfer->vehicle->displayLabel() }}</span>
                        <span class="block text-xs text-muted">{{ $transfer->vehicle->typeLabel() }} · ready for tracking</span>
                    @else
                        <span class="text-sm text-muted">Not recorded</span>
                    @endif
                </x-module.detail-field>
                <x-module.detail-field label="Dispatched" :value="$transfer->formatSentDate()" />
                <x-module.detail-field label="Status">
                    <x-module.status-badge :variant="$transfer->status" :label="logisticsTransferStatusLabel($transfer->status)" />
                </x-module.detail-field>
                <x-module.detail-field label="Modilon inventory">
                    @if($transfer->destinationDrug)
                        <a href="{{ getDashboardDrugRoute('show', $transfer->destinationDrug) }}" class="module-table-link">
                            Batch {{ $transfer->destinationDrug->batch_number }} · {{ number_format($transfer->destinationDrug->quantity_on_hand) }} on hand
                        </a>
                    @else
                        <span class="text-sm text-muted">Not added until pharmacy confirms receipt</span>
                    @endif
                </x-module.detail-field>
                <x-module.detail-field label="Dispatched by" :value="$transfer->sender->name ?? 'N/A'" />
                @if($transfer->receiver)
                    <x-module.detail-field label="Received by" :value="$transfer->receiver->name . ' · ' . $transfer->received_at?->format('M d, Y')" />
                @endif
            </dl>

            @if($transfer->hospitalOrder)
                <a href="{{ getDashboardHospitalOrderRoute('show', $transfer->hospitalOrder) }}" class="module-table-link mt-4 inline-flex text-sm">
                    Linked order {{ $transfer->hospitalOrder->order_number }} →
                </a>
            @endif

            @if($transfer->notes)
                <p class="mt-4 text-sm text-ink-secondary dark:text-zinc-300">{{ $transfer->notes }}</p>
            @endif
        </x-module.detail-card>
    </x-page-container>
</x-app-layout>
