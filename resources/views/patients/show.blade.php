<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">{{ $patient->full_name }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardPatientRoute('index')" label="Back to Patients" class="mb-6" />

        <div class="mb-6 flex flex-wrap gap-3">
            @if(auth()->user()->hasAnyRole(['pharmacist', 'pharmacy_manager']))
                <a href="{{ getDashboardPatientRoute('edit', $patient) }}" class="btn-module-secondary">Edit patient</a>
            @endif
            @if(auth()->user()->hasRole('pharmacist') && $patient->is_active)
                <a href="{{ getDashboardDispensingRoute('create') }}?patient_id={{ $patient->id }}" class="btn-brand text-xs uppercase tracking-wider">Dispense medicine</a>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="module-panel space-y-3 p-6 text-sm lg:col-span-1">
                <p><span class="font-medium text-muted">Patient #</span><br><span class="font-semibold text-ink">{{ $patient->patient_number }}</span></p>
                <p><span class="font-medium text-muted">Gender</span><br>{{ $patient->genderLabel() }}</p>
                <p><span class="font-medium text-muted">Date of birth</span><br>{{ $patient->date_of_birth?->format('d M Y') ?: '—' }}</p>
                <p><span class="font-medium text-muted">Phone</span><br>{{ $patient->phone ?: '—' }}</p>
                <p><span class="font-medium text-muted">Facility</span><br>{{ $patient->facility }}</p>
                <p><span class="font-medium text-muted">Status</span><br>{{ $patient->is_active ? 'Active' : 'Inactive' }}</p>
            </div>

            <div class="module-panel p-6 lg:col-span-2">
                <h3 class="mb-4 font-display text-base font-semibold text-ink">Recent dispensing</h3>
                <div class="module-table-wrap overflow-x-auto">
                    <table class="module-table">
                        <thead>
                            <tr>
                                <th>Record #</th>
                                <th>Medicine</th>
                                <th>Qty</th>
                                <th>Date</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patient->dispensingRecords as $record)
                                <tr>
                                    <td class="font-semibold">{{ $record->record_number }}</td>
                                    <td>{{ $record->drug->drug_name ?? '—' }} ({{ $record->drug->batch_number ?? '' }})</td>
                                    <td>{{ number_format($record->quantity_dispensed) }} {{ $record->drug->unit ?? '' }}</td>
                                    <td>{{ $record->dispensed_at->format('d M Y H:i') }}</td>
                                    <td class="text-right">
                                        <a href="{{ getDashboardDispensingRoute('show', $record) }}" class="module-table-action">View</a>
                                    </td>
                                </tr>
                            @empty
                                <x-module.empty-row
                                    :colspan="5"
                                    title="No dispensing yet"
                                    description="Dispense Modilon stock to this patient from the pharmacy counter."
                                    :action-url="auth()->user()->hasRole('pharmacist') && $patient->is_active ? getDashboardDispensingRoute('create').'?patient_id='.$patient->id : null"
                                    :action-label="auth()->user()->hasRole('pharmacist') && $patient->is_active ? 'Dispense medicine' : null"
                                />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
