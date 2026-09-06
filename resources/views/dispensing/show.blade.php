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
            <!-- Patient Details -->
            <div class="rounded-xl border border-line bg-canvas p-4">
                <h3 class="mb-3 font-semibold text-ink">Patient Details</h3>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <p><span class="font-medium text-muted">Patient</span><br>
                        <a href="{{ getDashboardPatientRoute('show', $record->patient) }}" class="font-semibold text-brand-600 hover:underline">
                            {{ $record->patient->full_name ?? '—' }}
                        </a>
                    </p>
                    <p><span class="font-medium text-muted">Hospital Number</span><br>{{ $record->patient->patient_number ?? '—' }}</p>
                    <p><span class="font-medium text-muted">Age / DOB</span><br>{{ $record->patient->date_of_birth?->format('d M Y') ?? '—' }}</p>
                    <p><span class="font-medium text-muted">Sex</span><br>{{ $record->patient->genderLabel() }}</p>
                    <p><span class="font-medium text-muted">Ward / Clinic</span><br>{{ $record->patient->ward_clinic ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Facility</span><br>{{ $record->patient->facility ?? '—' }}</p>
                </div>
            </div>

            <!-- Prescription Information -->
            <div class="rounded-xl border border-line bg-canvas p-4">
                <h3 class="mb-3 font-semibold text-ink">Prescription Information</h3>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <p><span class="font-medium text-muted">Date of Prescription</span><br>{{ $record->prescription_date?->format('d M Y') ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Diagnosis</span><br>{{ $record->diagnosis ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Medicine</span><br>{{ $record->drug->drug_name ?? '—' }} ({{ $record->drug->dosage ?? '' }})</p>
                    <p><span class="font-medium text-muted">Dosage Form</span><br>{{ $record->dosage_form ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Strength</span><br>{{ $record->strength ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Quantity to Dispense</span><br>{{ $record->quantity_to_dispense ? number_format($record->quantity_to_dispense) : '—' }}</p>
                    <p><span class="font-medium text-muted">Quantity Dispensed</span><br>{{ number_format($record->quantity_dispensed) }} {{ $record->drug->unit ?? '' }}</p>
                    <p><span class="font-medium text-muted">Batch</span><br>{{ $record->drug->batch_number ?? '—' }}</p>
                    <p><span class="font-medium text-muted">Expiry</span><br>{{ $record->drug?->expiry_date?->format('d M Y') ?: '—' }}</p>
                </div>
                @if($record->directions_for_use)
                    <p class="mt-3"><span class="font-medium text-muted">Directions for Use</span><br>{{ $record->directions_for_use }}</p>
                @endif
                <p class="mt-3"><span class="font-medium text-muted">Prescribed Dose</span><br>{{ $record->prescribed_dose ?: '—' }}</p>
            </div>

            <!-- Prescriber Details -->
            <div class="rounded-xl border border-line bg-canvas p-4">
                <h3 class="mb-3 font-semibold text-ink">Prescriber Details</h3>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <p><span class="font-medium text-muted">Doctor's Name</span><br>{{ $record->prescriber_name ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Designation</span><br>{{ $record->prescriber_designation ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Signature</span><br>{{ $record->prescriber_signature ?: '—' }}</p>
                </div>
                <p class="mt-3"><span class="font-medium text-muted">Hospital Stamp</span><br>{{ $record->hospital_stamp ? '✓ Yes' : '— No' }}</p>
            </div>

            <!-- Pharmacy / Dispensing Section -->
            <div class="rounded-xl border border-line bg-canvas p-4">
                <h3 class="mb-3 font-semibold text-ink">Pharmacy / Dispensing Section</h3>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <p><span class="font-medium text-muted">Pharmacist Signature</span><br>{{ $record->pharmacist_signature ?: '—' }}</p>
                    <p><span class="font-medium text-muted">Dispensed At</span><br>{{ $record->dispensed_at->format('d M Y H:i') }}</p>
                    <p><span class="font-medium text-muted">Dispensed By</span><br>{{ $record->dispenser->name ?? '—' }}</p>
                </div>
                <p class="mt-3"><span class="font-medium text-muted">Prescription / Rx Ref</span><br>{{ $record->prescription_ref ?: '—' }}</p>
                @if($record->dispensing_notes)
                    <p class="mt-3"><span class="font-medium text-muted">Dispensing Notes</span><br>{{ $record->dispensing_notes }}</p>
                @endif
            </div>

            <!-- Additional Notes -->
            @if($record->allergies_contraindications || $record->repeat_instructions || $record->follow_up_instructions)
                <div class="rounded-xl border border-line bg-canvas p-4">
                    <h3 class="mb-3 font-semibold text-ink">Additional Notes</h3>
                    @if($record->allergies_contraindications)
                        <p class="mb-3"><span class="font-medium text-muted">Allergies / Contraindications</span><br>{{ $record->allergies_contraindications }}</p>
                    @endif
                    @if($record->repeat_instructions)
                        <p class="mb-3"><span class="font-medium text-muted">Repeat Instructions</span><br>{{ $record->repeat_instructions }}</p>
                    @endif
                    @if($record->follow_up_instructions)
                        <p><span class="font-medium text-muted">Follow-up Instructions</span><br>{{ $record->follow_up_instructions }}</p>
                    @endif
                </div>
            @endif

            <!-- Prescription Audit -->
            <div class="rounded-xl border border-line bg-canvas p-4">
                <h3 class="mb-3 font-semibold text-ink">Prescription Audit</h3>
                <ul class="space-y-1 text-sm">
                    <li>{{ $record->audit_date_checked ? '✓' : '—' }} Prescription date checked</li>
                    <li>{{ $record->audit_prescriber_checked ? '✓' : '—' }} Prescriber details verified</li>
                    <li>{{ $record->audit_drug_dose_checked ? '✓' : '—' }} Drug and dose match prescription</li>
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
