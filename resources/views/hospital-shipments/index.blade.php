<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Logistics</p>
            <h2 class="heading-page">Hospital Road Deliveries</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="truck"
            description="Track drugs dispatched by road from Lae AMS to Modilon Hospital — each delivery is linked to an assigned vehicle."
        />

        <div class="module-panel p-6">
            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                                <th>Transfer #</th>
                                <th>Medicines</th>
                                <th>Vehicle</th>
                                <th>Qty</th>
                                <th>Sent</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transfers as $transfer)
                                <tr>
                                    <td class="font-semibold text-ink dark:text-zinc-100">{{ $transfer->transfer_number }}</td>
                                    <td>{{ $transfer->medicinesLabel() }}</td>
                                <td>
                                    @if($transfer->vehicle)
                                        <span class="font-medium text-ink dark:text-zinc-100">{{ $transfer->vehicle->registration }}</span>
                                        <span class="block text-xs text-muted">{{ $transfer->vehicle->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ number_format($transfer->quantity_sent) }}</td>
                                <td>{{ $transfer->formatSentDate() }}</td>
                                <td>
                                    <x-module.status-badge :variant="$transfer->status" :label="logisticsTransferStatusLabel($transfer->status)" />
                                </td>
                                <td class="text-right">
                                    <div class="module-table-actions">
                                        <a href="{{ getDashboardHospitalShipmentRoute('show', $transfer) }}" class="module-table-action">View</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-module.empty-row
                                :colspan="7"
                                title="No hospital road deliveries yet"
                                description="Deliveries appear when Lae AMS dispatches approved hospital orders."
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transfers->hasPages())
                <div class="mt-6">{{ $transfers->links() }}</div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
