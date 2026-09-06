<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Hospital Wards</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="building-hospital"
            description="Manage hospital wards for inpatient drug issuance and inventory tracking."
            :action-url="getDashboardWardRoute('create')"
            action-label="Add ward"
        />

        <div class="module-panel p-6">
            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Ward #</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Bed Capacity</th>
                            <th>Stock Items</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wards as $ward)
                            <tr>
                                <td class="font-semibold text-ink">{{ $ward->ward_number }}</td>
                                <td>{{ $ward->name }}</td>
                                <td>{{ $ward->typeLabel() }}</td>
                                <td>{{ $ward->location ?: '—' }}</td>
                                <td>{{ $ward->bed_capacity ?: '—' }}</td>
                                <td>{{ number_format($ward->wardStocks->count()) }}</td>
                                <td>
                                    <x-module.status-badge
                                        :variant="$ward->is_active ? 'received' : 'cancelled'"
                                        :label="$ward->is_active ? 'Active' : 'Inactive'"
                                    />
                                </td>
                                <td class="text-right">
                                    <div class="module-table-actions">
                                        <a href="{{ getDashboardWardRoute('show', $ward) }}" class="module-table-action">View</a>
                                        <a href="{{ getDashboardWardRoute('edit', $ward) }}" class="module-table-action">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-module.empty-row
                                :colspan="8"
                                title="No wards found"
                                description="Add a ward to begin tracking inpatient drug inventory."
                                :action-url="getDashboardWardRoute('create')"
                                action-label="Add ward"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
