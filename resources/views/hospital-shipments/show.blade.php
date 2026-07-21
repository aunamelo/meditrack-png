<x-app-layout>
    <x-slot name="header">
        <div><p class="text-section-label">Logistics</p><h2 class="heading-page">{{ $transfer->transfer_number }}</h2></div>
    </x-slot>
    <x-page-container>
        <div class="surface-panel p-6 space-y-4">
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-gray-500">Drug</dt><dd class="font-medium">{{ $transfer->drug->drug_name ?? 'N/A' }} ({{ $transfer->drug->dosage ?? '' }})</dd></div>
                <div><dt class="text-gray-500">Quantity sent</dt><dd class="font-medium">{{ number_format($transfer->quantity_sent) }}</dd></div>
                <div><dt class="text-gray-500">Batch</dt><dd class="font-medium">{{ $transfer->batch_number }}</dd></div>
                <div><dt class="text-gray-500">Route</dt><dd class="font-medium">Lae AMS → Modilon Hospital (by road)</dd></div>
                <div><dt class="text-gray-500">Transport</dt><dd class="font-medium">Land — car/truck</dd></div>
                <div><dt class="text-gray-500">Dispatched</dt><dd class="font-medium">{{ $transfer->formatSentDate() }}</dd></div>
                <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ logisticsTransferStatusLabel($transfer->status) }}</dd></div>
                <div><dt class="text-gray-500">Dispatched by</dt><dd class="font-medium">{{ $transfer->sender->name ?? 'N/A' }}</dd></div>
                @if($transfer->receiver)<div><dt class="text-gray-500">Received by</dt><dd class="font-medium">{{ $transfer->receiver->name }} · {{ $transfer->received_at?->format('M d, Y') }}</dd></div>@endif
            </dl>
            @if($transfer->hospitalOrder)
                <a href="{{ getDashboardHospitalOrderRoute('show', $transfer->hospitalOrder) }}" class="text-sm font-semibold text-brand-600">Linked order {{ $transfer->hospitalOrder->order_number }} →</a>
            @endif
            @if($transfer->notes)<p class="text-sm text-gray-600">{{ $transfer->notes }}</p>@endif
            <a href="{{ getDashboardHospitalShipmentRoute('index') }}" class="inline-block text-sm font-semibold text-gray-600">← Back to road deliveries</a>
        </div>
    </x-page-container>
</x-app-layout>
