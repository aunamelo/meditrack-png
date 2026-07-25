<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Procurement</p>
            <h2 class="heading-page">Medicine Catalog</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="clipboard"
            description="Essential medicines procured from registered India & China manufacturers (NDoH import policy)"
            :action-url="getDashboardMedicineRoute('create')"
            action-label="Add medicine"
        />

        <div class="module-panel p-6">
            <form action="{{ getDashboardMedicineRoute('index') }}" method="GET" class="module-filter mb-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Medicine name or dosage..." class="input-field">
                    </div>
                    <div>
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="input-field">
                            <option value="">Active only</option>
                            <option value="all" @selected(request('status') === 'all')>All</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Filter</button>
                    <a href="{{ getDashboardMedicineRoute('index') }}" class="btn-module-secondary">Clear</a>
                </div>
            </form>

            @if($medicines->count())
                <div class="module-table-wrap overflow-x-auto">
                    <table class="module-table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Form</th>
                                <th>Registered supplier</th>
                                <th>Unit</th>
                                <th class="text-right">Reorder point</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($medicines as $medicine)
                                <tr>
                                    <td>
                                        <span class="font-medium text-ink dark:text-zinc-100">{{ $medicine->name }}</span>
                                        <span class="block text-xs text-muted">{{ $medicine->dosage }}</span>
                                    </td>
                                    <td>{{ $medicine->formLabel() }}</td>
                                    <td>
                                        @if($medicine->supplier)
                                            <span class="font-medium text-ink dark:text-zinc-100">{{ $medicine->supplier->name }}</span>
                                            <span class="block text-xs text-muted">{{ $medicine->supplier->countryLabel() }}</span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>{{ $medicine->unit }}</td>
                                    <td class="text-right">{{ number_format($medicine->reorder_point) }}</td>
                                    <td>
                                        <x-module.status-badge :variant="$medicine->is_active ? 'green' : 'gray'" :label="$medicine->is_active ? 'Active' : 'Inactive'" />
                                    </td>
                                    <td class="text-right whitespace-nowrap">
                                        <div class="module-table-actions">
                                            <a href="{{ getDashboardMedicineRoute('show', $medicine) }}" class="module-table-action">View</a>
                                            <a href="{{ getDashboardMedicineRoute('edit', $medicine) }}" class="module-table-action module-table-action-edit">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $medicines->withQueryString()->links() }}</div>
            @else
                <div class="module-empty py-12">
                    <div class="module-empty-icon">
                        <x-dashboard.icon name="clipboard" class="h-6 w-6 text-muted" />
                    </div>
                    <p class="text-sm font-semibold text-ink dark:text-zinc-200">No medicines in catalog</p>
                    <p class="mt-1 text-sm text-muted">Add approved medicines here before creating procurement orders.</p>
                    <a href="{{ getDashboardMedicineRoute('create') }}" class="btn-brand mt-4 text-xs uppercase tracking-wider">Add medicine</a>
                </div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
