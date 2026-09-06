<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Drug Issuances</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="arrow-right-circle"
            description="Track drugs issued from pharmacy to hospital wards."
            :action-url="getDashboardDrugIssuanceRoute('create')"
            action-label="Issue drugs"
        />

        <div class="module-panel p-6">
            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Issuance #</th>
                            <th>Date</th>
                            <th>Ward</th>
                            <th>Drug</th>
                            <th>Batch</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issuances as $issuance)
                            <tr>
                                <td class="font-semibold text-ink">{{ $issuance->issuance_number }}</td>
                                <td>{{ $issuance->issuance_date->format('d M Y') }}</td>
                                <td>{{ $issuance->ward->name }}</td>
                                <td>{{ $issuance->drug->drug_name }}</td>
                                <td>{{ $issuance->batch_number }}</td>
                                <td>{{ number_format($issuance->quantity_issued) }} {{ $issuance->drug->unit }}</td>
                                <td>
                                    <x-module.status-badge
                                        :variant="$issuance->isReceived() ? 'received' : 'sent'"
                                        :label="$issuance->isReceived() ? 'Received' : 'Pending'"
                                    />
                                </td>
                                <td class="text-right">
                                    <div class="module-table-actions">
                                        <a href="{{ getDashboardDrugIssuanceRoute('show', $issuance) }}" class="module-table-action">View</a>
                                        @if(!$issuance->isReceived())
                                            <form action="{{ getDashboardDrugIssuanceRoute('receive', $issuance) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="module-table-action">Receive</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-module.empty-row
                                :colspan="8"
                                title="No issuances found"
                                description="Issue drugs to wards to begin tracking inpatient inventory."
                                :action-url="getDashboardDrugIssuanceRoute('create')"
                                action-label="Issue drugs"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $issuances->links() }}</div>
        </div>
    </x-page-container>
</x-app-layout>
