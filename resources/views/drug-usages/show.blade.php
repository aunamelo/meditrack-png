<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Drug Usage Details</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardDrugUsageRoute('index')" label="Back to usage log" class="mb-6" />

        <div class="space-y-6">
            <div class="module-panel p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-ink">Usage Details</h3>
                    <form action="{{ getDashboardDrugUsageRoute('destroy', $usage) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this usage record? Ward stock will be restored.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-module-danger text-sm">Delete</button>
                    </form>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                    <p><span class="font-medium text-muted">Date</span><br>{{ $usage->usage_date->format('d M Y H:i') }}</p>
                    <p><span class="font-medium text-muted">Ward</span><br>{{ $usage->ward->name }}</p>
                    <p><span class="font-medium text-muted">Usage Type</span><br>{{ $usage->usageTypeLabel() }}</p>
                </div>
            </div>

            <div class="module-panel p-6">
                <h3 class="text-lg font-semibold text-ink mb-4">Drug Information</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                    <p><span class="font-medium text-muted">Drug</span><br>{{ $usage->drug->drug_name }}</p>
                    <p><span class="font-medium text-muted">Batch</span><br>{{ $usage->drug->batch_number }}</p>
                    <p><span class="font-medium text-muted">Quantity Used</span><br class="font-semibold">{{ number_format($usage->quantity_used) }} {{ $usage->drug->unit }}</p>
                </div>
            </div>

            <div class="module-panel p-6">
                <h3 class="text-lg font-semibold text-ink mb-4">Patient Information</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                    <p><span class="font-medium text-muted">Patient</span><br>
                        @if($usage->patient)
                            <a href="{{ getDashboardPatientRoute('show', $usage->patient) }}" class="text-brand-600 hover:underline">{{ $usage->patient->full_name }}</a>
                        @else
                            —
                        @endif
                    </p>
                    <p><span class="font-medium text-muted">Recorded By</span><br>{{ $usage->recorded_by ?? $usage->creator->name }}</p>
                </div>
            </div>

            @if($usage->notes)
                <div class="module-panel p-6">
                    <h3 class="text-lg font-semibold text-ink mb-4">Notes</h3>
                    <p class="text-sm">{{ $usage->notes }}</p>
                </div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
