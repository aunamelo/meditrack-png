<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Warehouse ops</p>
            <h2 class="heading-page">Registered Vehicles</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="truck"
            description="Lae AMS road fleet used for Modilon Hospital deliveries. Active vehicles appear in the ship-order vehicle list."
            :action-url="getDashboardVehicleRoute('create')"
            action-label="Register vehicle"
        />

        <div class="module-panel p-6">
            <form action="{{ getDashboardVehicleRoute('index') }}" method="GET" class="module-filter mb-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name or registration..." class="input-field">
                    </div>
                    <div>
                        <label for="type" class="form-label">Type</label>
                        <select name="type" id="type" class="input-field">
                            <option value="">All types</option>
                            @foreach(['truck' => 'Truck', 'van' => 'Van', 'ute' => 'Ute'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
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
                    <a href="{{ getDashboardVehicleRoute('index') }}" class="btn-module-secondary">Clear</a>
                </div>
            </form>

            @if($vehicles->count())
                <div class="module-table-wrap overflow-x-auto">
                    <table class="module-table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Registration</th>
                                <th>Type</th>
                                <th>Depot</th>
                                <th>Status</th>
                                <th>On road</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $vehicle)
                                @php $activeShipment = $activeShipments->get($vehicle->id); @endphp
                                <tr>
                                    <td>
                                        <span class="font-medium text-ink dark:text-zinc-100">{{ $vehicle->name }}</span>
                                    </td>
                                    <td class="tabular-nums">{{ $vehicle->registration }}</td>
                                    <td>{{ $vehicle->typeLabel() }}</td>
                                    <td>Lae AMS</td>
                                    <td>
                                        <x-module.status-badge :variant="$vehicle->is_active ? 'active' : 'default'" :label="$vehicle->is_active ? 'Active' : 'Inactive'" />
                                    </td>
                                    <td>
                                        @if($activeShipment)
                                            <span class="text-sm font-medium text-amber-700 dark:text-amber-300">{{ $activeShipment->transfer_number }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right whitespace-nowrap">
                                        <div class="module-table-actions">
                                            <a href="{{ getDashboardVehicleRoute('show', $vehicle) }}" class="module-table-action">View</a>
                                            <a href="{{ getDashboardVehicleRoute('edit', $vehicle) }}" class="module-table-action module-table-action-edit">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $vehicles->links() }}</div>
            @else
                <div class="module-empty py-12">
                    <div class="module-empty-icon">
                        <x-dashboard.icon name="truck" class="h-6 w-6 text-muted" />
                    </div>
                    <p class="text-sm font-semibold text-ink dark:text-zinc-200">No registered vehicles</p>
                    <p class="mt-1 text-sm text-muted">Register Lae AMS fleet vehicles before shipping hospital road deliveries.</p>
                    <a href="{{ getDashboardVehicleRoute('create') }}" class="btn-brand mt-4 text-xs uppercase tracking-wider">Register vehicle</a>
                </div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
