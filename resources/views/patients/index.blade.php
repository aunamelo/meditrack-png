<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Patients</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="users"
            description="Register and manage patients for outpatient dispensing at Modilon General Hospital."
            :action-url="auth()->user()->hasAnyRole(['pharmacist', 'pharmacy_manager']) ? getDashboardPatientRoute('create') : null"
            :action-label="auth()->user()->hasAnyRole(['pharmacist', 'pharmacy_manager']) ? 'Register patient' : null"
        />

        <div class="module-panel p-6">
            <form method="GET" class="module-filter mb-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Patient #, name, or phone..." class="input-field">
                    </div>
                    <div>
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="input-field">
                            <option value="">All</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Filter</button>
                    <a href="{{ getDashboardPatientRoute('index') }}" class="btn-module-secondary">Clear</a>
                </div>
            </form>

            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Patient #</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Dispenses</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $patient)
                            <tr>
                                <td class="font-semibold text-ink dark:text-zinc-100">{{ $patient->patient_number }}</td>
                                <td>{{ $patient->full_name }}</td>
                                <td>{{ $patient->genderLabel() }}</td>
                                <td>{{ $patient->phone ?: '—' }}</td>
                                <td>{{ number_format($patient->dispensing_records_count) }}</td>
                                <td>
                                    <x-module.status-badge
                                        :variant="$patient->is_active ? 'received' : 'cancelled'"
                                        :label="$patient->is_active ? 'Active' : 'Inactive'"
                                    />
                                </td>
                                <td class="text-right">
                                    <div class="module-table-actions">
                                        <a href="{{ getDashboardPatientRoute('show', $patient) }}" class="module-table-action">View</a>
                                        @if(auth()->user()->hasRole('pharmacist'))
                                            <a href="{{ getDashboardDispensingRoute('create') }}?patient_id={{ $patient->id }}" class="module-table-action">Dispense</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-module.empty-row
                                :colspan="7"
                                title="No patients found"
                                description="Register a patient to begin dispensing at Modilon."
                                :action-url="getDashboardPatientRoute('create')"
                                action-label="Register patient"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $patients->links() }}</div>
        </div>
    </x-page-container>
</x-app-layout>
