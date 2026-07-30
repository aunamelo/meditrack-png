<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Inventory</p>
            <h2 class="heading-page">{{ $stockAdjustment->adjustment_number }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardStockAdjustmentRoute('index')" label="Back to Stock Takes" class="mb-6" />

        <div class="surface-panel p-6">
            <dl class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                <div>
                    <dt class="text-xs uppercase text-gray-500">Medicine</dt>
                    <dd class="font-semibold">{{ $stockAdjustment->drug?->drug_name }} ({{ $stockAdjustment->drug?->dosage }})</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Batch</dt>
                    <dd class="font-semibold">{{ $stockAdjustment->drug?->batch_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">System quantity</dt>
                    <dd class="tabular-nums">{{ number_format($stockAdjustment->quantity_system) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Counted quantity</dt>
                    <dd class="tabular-nums">{{ number_format($stockAdjustment->quantity_counted) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Difference</dt>
                    <dd class="font-semibold tabular-nums">{{ $stockAdjustment->quantity_delta > 0 ? '+' : '' }}{{ number_format($stockAdjustment->quantity_delta) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Reason</dt>
                    <dd>{{ $stockAdjustment->reasonLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase text-gray-500">Posted by</dt>
                    <dd>{{ $stockAdjustment->adjuster?->name }} · {{ $stockAdjustment->adjusted_at?->format('d M Y H:i') }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-xs uppercase text-gray-500">Notes</dt>
                    <dd>{{ $stockAdjustment->notes ?: '—' }}</dd>
                </div>
            </dl>
        </div>
    </x-page-container>
</x-app-layout>
