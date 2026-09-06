<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Drug Returns</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="arrow-left-circle"
            description="Track drugs returned from hospital wards to pharmacy."
            :action-url="getDashboardDrugReturnRoute('create')"
            action-label="Return drugs"
        />

        <div class="module-panel p-6">
            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Return #</th>
                            <th>Date</th>
                            <th>Ward</th>
                            <th>Drug</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr>
                                <td class="font-semibold text-ink">{{ $return->return_number }}</td>
                                <td>{{ $return->return_date->format('d M Y') }}</td>
                                <td>{{ $return->ward->name }}</td>
                                <td>{{ $return->drug->drug_name }}</td>
                                <td>{{ number_format($return->quantity_returned) }} {{ $return->drug->unit }}</td>
                                <td>{{ $return->returnReasonLabel() }}</td>
                                <td>
                                    <x-module.status-badge
                                        :variant="$return->isReceived() ? 'received' : 'sent'"
                                        :label="$return->isReceived() ? 'Received' : 'Pending'"
                                    />
                                </td>
                                <td class="text-right">
                                    <div class="module-table-actions">
                                        <a href="{{ getDashboardDrugReturnRoute('show', $return) }}" class="module-table-action">View</a>
                                        @if(!$return->isReceived())
                                            <form action="{{ getDashboardDrugReturnRoute('receive', $return) }}" method="POST" class="inline">
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
                                title="No returns found"
                                description="Return drugs from wards to pharmacy."
                                :action-url="getDashboardDrugReturnRoute('create')"
                                action-label="Return drugs"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $returns->links() }}</div>
        </div>
    </x-page-container>
</x-app-layout>
