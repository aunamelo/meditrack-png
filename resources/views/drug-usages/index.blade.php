<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Drug Usage Log</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="clipboard-list"
            description="Track drug administration and usage in hospital wards."
        />

        <div class="module-panel p-6">
            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Ward</th>
                            <th>Drug</th>
                            <th>Patient</th>
                            <th>Quantity</th>
                            <th>Type</th>
                            <th>Recorded By</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usages as $usage)
                            <tr>
                                <td>{{ $usage->usage_date->format('d M Y H:i') }}</td>
                                <td>{{ $usage->ward->name }}</td>
                                <td>{{ $usage->drug->drug_name }}</td>
                                <td>{{ $usage->patient?->full_name ?? '—' }}</td>
                                <td>{{ number_format($usage->quantity_used) }} {{ $usage->drug->unit }}</td>
                                <td>{{ $usage->usageTypeLabel() }}</td>
                                <td>{{ $usage->recorded_by ?? $usage->creator->name }}</td>
                                <td class="text-right">
                                    <div class="module-table-actions">
                                        <a href="{{ getDashboardDrugUsageRoute('show', $usage) }}" class="module-table-action">View</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-module.empty-row
                                :colspan="8"
                                title="No usage records found"
                                description="Log drug usage to track consumption in wards."
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $usages->links() }}</div>
        </div>
    </x-page-container>
</x-app-layout>
