<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">{{ $record->record_number }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardDispensingRoute('index')" label="Back to Dispensing" class="mb-6" />

        <div class="module-panel space-y-4 p-6 text-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <p><span class="font-medium text-muted">Patient</span><br>
                    <a href="{{ getDashboardPatientRoute('show', $record->patient) }}" class="font-semibold text-brand-600 hover:underline">
                        {{ $record->patient->full_name ?? '—' }} ({{ $record->patient->patient_number ?? '' }})
                    </a>
                </p>
                <p><span class="font-medium text-muted">Dispensed by</span><br>{{ $record->dispenser->name ?? '—' }}</p>
                <p><span class="font-medium text-muted">Medicine</span><br>{{ $record->drug->drug_name ?? '—' }} ({{ $record->drug->dosage ?? '' }})</p>
                <p><span class="font-medium text-muted">Batch</span><br>{{ $record->drug->batch_number ?? '—' }}</p>
                <p><span class="font-medium text-muted">Expiry</span><br>{{ $record->drug?->expiry_date?->format('d M Y') ?: '—' }}</p>
                <p><span class="font-medium text-muted">Quantity</span><br>{{ number_format($record->quantity_dispensed) }} {{ $record->drug->unit ?? '' }}</p>
                <p><span class="font-medium text-muted">Dispensed at</span><br>{{ $record->dispensed_at->format('d M Y H:i') }}</p>
                <p><span class="font-medium text-muted">Prescription ref</span><br>{{ $record->prescription_ref ?: '—' }}</p>
            </div>

            <div class="rounded-xl border border-line bg-canvas p-4">
                <h3 class="mb-3 font-semibold text-ink">Prescription audit</h3>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <p><span class="font-medium text-muted">Rx date</span><br>{{ $record->prescription_date?->format('d M Y') ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Prescriber</span><br>{{ $record->prescriber_name ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Prescribed dose</span><br>{{ $record->prescribed_dose ?: '—' }}</p>
                </div>
                <ul class="mt-3 space-y-1 text-sm">
                    <li>{{ $record->audit_date_checked ? '✓' : '—' }} Date checked</li>
                    <li>{{ $record->audit_prescriber_checked ? '✓' : '—' }} Prescriber checked</li>
                    <li>{{ $record->audit_drug_dose_checked ? '✓' : '—' }} Drug / dose matched</li>
                    <li>{{ $record->audit_contraindications_checked ? '✓' : '—' }} Contraindications / allergies checked</li>
                </ul>
            </div>

            @if($record->notes)
                <p class="rounded-lg bg-canvas p-3 text-ink"><span class="font-medium text-muted">Notes:</span> {{ $record->notes }}</p>
            @endif
            @if($record->drug)
                <a href="{{ getDashboardDrugRoute('show', $record->drug) }}" class="inline-flex font-semibold text-brand-600 hover:underline">View stock batch →</a>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
