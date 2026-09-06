<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">{{ $return->return_number }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardDrugReturnRoute('index')" label="Back to returns" class="mb-6" />

        <div class="space-y-6">
            <!-- Return Details -->
            <div class="module-panel p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-ink">Return Details</h3>
                    @if(!$return->isReceived())
                        <div class="flex gap-2">
                            <form action="{{ getDashboardDrugReturnRoute('receive', $return) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-brand text-sm">Receive & Restore Stock</button>
                            </form>
                            <form action="{{ getDashboardDrugReturnRoute('destroy', $return) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this return? Ward stock will be restored.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-module-danger text-sm">Delete</button>
                            </form>
                        </div>
                    @endif
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                    <p><span class="font-medium text-muted">Date</span><br>{{ $return->return_date->format('d M Y') }}</p>
                    <p><span class="font-medium text-muted">Ward</span><br>{{ $return->ward->name }}</p>
                    <p><span class="font-medium text-muted">Reason</span><br>{{ $return->returnReasonLabel() }}</p>
                    <p><span class="font-medium text-muted">Status</span><br>
                        <x-module.status-badge
                            :variant="$return->isReceived() ? 'received' : 'sent'"
                            :label="$return->isReceived() ? 'Received' : 'Pending'"
                        />
                    </p>
                </div>
            </div>

            <!-- Drug Details -->
            <div class="module-panel p-6">
                <h3 class="text-lg font-semibold text-ink mb-4">Drug Information</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                    <p><span class="font-medium text-muted">Drug</span><br>{{ $return->drug->drug_name }}</p>
                    <p><span class="font-medium text-muted">Batch Number</span><br>{{ $return->batch_number }}</p>
                    <p><span class="font-medium text-muted">Quantity Returned</span><br class="font-semibold">{{ number_format($return->quantity_returned) }} {{ $return->drug->unit }}</p>
                </div>
            </div>

            <!-- People -->
            <div class="module-panel p-6">
                <h3 class="text-lg font-semibold text-ink mb-4">People</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                    <p><span class="font-medium text-muted">Returned By</span><br>{{ $return->returner->name ?? '—' }}</p>
                    <p><span class="font-medium text-muted">Received By</span><br>{{ $return->receiver->name ?? '—' }}</p>
                    @if($return->received_at)
                        <p><span class="font-medium text-muted">Received At</span><br>{{ $return->received_at->format('d M Y H:i') }}</p>
                    @endif
                </div>
            </div>

            @if($return->notes)
                <div class="module-panel p-6">
                    <h3 class="text-lg font-semibold text-ink mb-4">Notes</h3>
                    <p class="text-sm">{{ $return->notes }}</p>
                </div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
