<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">{{ $issuance->issuance_number }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardDrugIssuanceRoute('index')" label="Back to issuances" class="mb-6" />

        <div class="space-y-6">
            <!-- Issuance Details -->
            <div class="module-panel p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-ink">Issuance Details</h3>
                    @if(!$issuance->isReceived())
                        <div class="flex gap-2">
                            <form action="{{ getDashboardDrugIssuanceRoute('receive', $issuance) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-brand text-sm">Mark as Received</button>
                            </form>
                            <form action="{{ getDashboardDrugIssuanceRoute('destroy', $issuance) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this issuance? Stock will be restored.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-module-danger text-sm">Delete</button>
                            </form>
                        </div>
                    @endif
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                    <p><span class="font-medium text-muted">Date</span><br>{{ $issuance->issuance_date->format('d M Y') }}</p>
                    <p><span class="font-medium text-muted">Ward</span><br>{{ $issuance->ward->name }}</p>
                    <p><span class="font-medium text-muted">Status</span><br>
                        <x-module.status-badge
                            :variant="$issuance->isReceived() ? 'received' : 'sent'"
                            :label="$issuance->isReceived() ? 'Received' : 'Pending'"
                        />
                    </p>
                </div>
            </div>

            <!-- Drug Details -->
            <div class="module-panel p-6">
                <h3 class="text-lg font-semibold text-ink mb-4">Drug Information</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                    <p><span class="font-medium text-muted">Drug</span><br>{{ $issuance->drug->drug_name }}</p>
                    <p><span class="font-medium text-muted">Batch Number</span><br>{{ $issuance->batch_number }}</p>
                    <p><span class="font-medium text-muted">Strength</span><br>{{ $issuance->strength ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Dosage Form</span><br>{{ $issuance->dosage_form ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Quantity Issued</span><br class="font-semibold">{{ number_format($issuance->quantity_issued) }} {{ $issuance->drug->unit }}</p>
                </div>
            </div>

            <!-- People -->
            <div class="module-panel p-6">
                <h3 class="text-lg font-semibold text-ink mb-4">People</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                    <p><span class="font-medium text-muted">Issued By</span><br>{{ $issuance->issuer->name ?? '—' }}</p>
                    <p><span class="font-medium text-muted">Received By</span><br>{{ $issuance->receiver->name ?? '—' }}</p>
                    @if($issuance->received_at)
                        <p><span class="font-medium text-muted">Received At</span><br>{{ $issuance->received_at->format('d M Y H:i') }}</p>
                    @endif
                </div>
            </div>

            @if($issuance->notes)
                <div class="module-panel p-6">
                    <h3 class="text-lg font-semibold text-ink mb-4">Notes</h3>
                    <p class="text-sm">{{ $issuance->notes }}</p>
                </div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
