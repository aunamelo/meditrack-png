<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Warehouse ops</p>
            <h2 class="heading-page">{{ $vehicle->name }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <div class="module-actions-bar">
            <x-module.back-link :href="getDashboardVehicleRoute('index')" label="Back to vehicles" />
            <div class="flex flex-wrap gap-3">
                <a href="{{ getDashboardVehicleRoute('edit', $vehicle) }}" class="btn-module-secondary">Edit</a>
                @if($vehicle->is_active)
                    <form
                        action="{{ getDashboardVehicleRoute('deactivate', $vehicle) }}"
                        method="POST"
                        class="inline"
                        data-confirm="Deactivate this vehicle? It will no longer appear when shipping hospital orders."
                        data-confirm-title="Deactivate vehicle"
                        data-confirm-label="Deactivate"
                        data-confirm-danger="1"
                    >
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-rose-700 hover:bg-rose-100 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300">
                            Deactivate
                        </button>
                    </form>
                @else
                    <form action="{{ getDashboardVehicleRoute('activate', $vehicle) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Activate</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-module.detail-card title="Fleet details">
                <dl class="space-y-4">
                    <x-module.detail-field label="Name" :value="$vehicle->name" />
                    <x-module.detail-field label="Registration" :value="$vehicle->registration" />
                    <x-module.detail-field label="Type" :value="$vehicle->typeLabel()" />
                    <x-module.detail-field label="Depot" value="Lae AMS" />
                    <x-module.detail-field label="Status">
                        <x-module.status-badge :variant="$vehicle->is_active ? 'active' : 'default'" :label="$vehicle->is_active ? 'Active' : 'Inactive'" />
                    </x-module.detail-field>
                    <x-module.detail-field label="Notes">{{ $vehicle->notes ?: '—' }}</x-module.detail-field>
                </dl>
            </x-module.detail-card>

            <x-module.detail-card title="Current assignment">
                <dl class="space-y-4">
                    @if($activeShipment)
                        <x-module.detail-field label="Active delivery">
                            <a href="{{ getDashboardHospitalShipmentRoute('show', $activeShipment) }}" class="font-medium text-health-700 hover:underline dark:text-health-300">
                                {{ $activeShipment->transfer_number }}
                            </a>
                        </x-module.detail-field>
                        <x-module.detail-field label="Sent" :value="optional($activeShipment->sent_date)->format('d M Y') ?? '—'" />
                        <x-module.detail-field label="ETA" :value="optional($activeShipment->expected_arrival_at)->format('d M Y H:i') ?? '—'" />
                    @else
                        <p class="text-sm text-muted">This vehicle is not currently assigned to a road delivery.</p>
                    @endif

                    @if($vehicle->hasKnownLocation())
                        <x-module.detail-field label="Last GPS ping" :value="$vehicle->last_ping_at->format('d M Y H:i')" />
                        <x-module.detail-field label="Tracking">
                            <x-module.status-badge
                                :variant="$vehicle->isTrackingStale() ? 'pending' : 'active'"
                                :label="$vehicle->isTrackingStale() ? 'Stale / offline' : 'Live'"
                            />
                        </x-module.detail-field>
                    @endif
                </dl>

                <div class="mt-4">
                    <a href="{{ getDashboardLiveMapRoute('index') }}" class="text-sm font-semibold text-health-700 hover:underline dark:text-health-300">
                        Open live delivery map →
                    </a>
                </div>
            </x-module.detail-card>
        </div>

        <div class="module-panel mt-6 p-6">
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-ink dark:text-zinc-100">Recent road deliveries</h3>

            @if($recentShipments->count())
                <div class="module-table-wrap overflow-x-auto">
                    <table class="module-table">
                        <thead>
                            <tr>
                                <th>Transfer</th>
                                <th>Hospital order</th>
                                <th>Sent</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentShipments as $shipment)
                                <tr>
                                    <td class="font-medium">{{ $shipment->transfer_number }}</td>
                                    <td>{{ $shipment->hospitalOrder?->order_number ?? '—' }}</td>
                                    <td>{{ optional($shipment->sent_date)->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        <x-module.status-badge
                                            :variant="$shipment->status === 'received' ? 'received' : ($shipment->status === 'sent' ? 'sent' : 'default')"
                                            :label="ucfirst($shipment->status)"
                                        />
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ getDashboardHospitalShipmentRoute('show', $shipment) }}" class="module-table-action">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-muted">No Modilon road deliveries recorded for this vehicle yet.</p>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
