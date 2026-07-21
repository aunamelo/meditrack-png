<x-app-layout>
    <x-slot name="header">
        <div><p class="text-section-label">Logistics</p><h2 class="heading-page">Hospital Road Deliveries</h2></div>
    </x-slot>
    <x-page-container>
        <div class="surface-panel p-6">
            <p class="mb-6 text-sm text-gray-500">Track drugs dispatched by car from Lae AMS to Modilon Hospital.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Transfer #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Drug</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Sent</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($transfers as $transfer)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium">{{ $transfer->transfer_number }}</td>
                                <td class="px-4 py-3 text-sm">{{ $transfer->drug->drug_name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">{{ number_format($transfer->quantity_sent) }}</td>
                                <td class="px-4 py-3 text-sm">{{ $transfer->formatSentDate() }}</td>
                                <td class="px-4 py-3 text-sm">{{ logisticsTransferStatusLabel($transfer->status) }}</td>
                                <td class="px-4 py-3 text-right text-sm"><a href="{{ getDashboardHospitalShipmentRoute('show', $transfer) }}" class="font-semibold text-brand-600">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No hospital road deliveries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transfers->hasPages())<div class="mt-6">{{ $transfers->links() }}</div>@endif
        </div>
    </x-page-container>
</x-app-layout>
