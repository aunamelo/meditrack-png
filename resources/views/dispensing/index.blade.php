<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Dispensing Records</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="pill"
            :description="auth()->user()->hasRole('pharmacist') && ! auth()->user()->hasRole('pharmacy_manager')
                ? 'Your Modilon dispensing audit trail — patient, drug, quantity, batch, and prescription checks.'
                : 'Track medicines dispensed from Modilon Hospital pharmacy stock to registered patients.'"
            :action-url="auth()->user()->hasRole('pharmacist') ? getDashboardDispensingRoute('create') : null"
            :action-label="auth()->user()->hasRole('pharmacist') ? 'Dispense medicine' : null"
        />

        <div class="module-panel p-6">
            <form method="GET" class="module-filter mb-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Record #, patient, medicine, or batch..." class="input-field">
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Filter</button>
                    <a href="{{ getDashboardDispensingRoute('index') }}" class="btn-module-secondary">Clear</a>
                </div>
            </form>

            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Record #</th>
                            <th>Patient</th>
                            <th>Medicine</th>
                            <th>Batch / Expiry</th>
                            <th>Qty</th>
                            <th>Dispensed by</th>
                            <th>Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td class="font-semibold text-ink">{{ $record->record_number }}</td>
                                <td>{{ $record->patient->full_name ?? '—' }}</td>
                                <td>{{ $record->drug->drug_name ?? '—' }}</td>
                                <td>
                                    {{ $record->drug->batch_number ?? '—' }}
                                    @if($record->drug?->expiry_date)
                                        <span class="block text-xs text-muted">Exp {{ $record->drug->expiry_date->format('d M Y') }}</span>
                                    @endif
                                </td>
                                <td>{{ number_format($record->quantity_dispensed) }} {{ $record->drug->unit ?? '' }}</td>
                                <td>{{ $record->dispenser->name ?? '—' }}</td>
                                <td>{{ $record->dispensed_at->format('d M Y H:i') }}</td>
                                <td class="text-right">
                                    <a href="{{ getDashboardDispensingRoute('show', $record) }}" class="module-table-action">View</a>
                                </td>
                            </tr>
                        @empty
                            <x-module.empty-row
                                :colspan="8"
                                title="No dispensing records"
                                description="Dispensed medicines will appear here."
                                :action-url="auth()->user()->hasRole('pharmacist') ? getDashboardDispensingRoute('create') : null"
                                :action-label="auth()->user()->hasRole('pharmacist') ? 'Dispense medicine' : null"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $records->links() }}</div>
        </div>
    </x-page-container>
</x-app-layout>
