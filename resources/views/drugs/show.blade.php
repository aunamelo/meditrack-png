<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Inventory</p>
            <h2 class="heading-page">{{ $drug->drug_name }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <div class="module-actions-bar">
            <x-module.back-link :href="getDashboardDrugRoute('index')" label="Back to Drugs" />
            <div class="flex flex-wrap items-center gap-3">
                @if(auth()->user()->hasRole('procurement_officer') && $drug->level == 'ndoh' || auth()->user()->hasRole('pharmacy_manager') && $drug->level == 'modilon_hospital' || auth()->user()->hasRole('admin'))
                    <a href="{{ getDashboardDrugRoute('edit', $drug->id) }}" class="btn-module-secondary">Edit</a>
                @endif
                @if(auth()->user()->hasRole('admin'))
                    <form action="{{ getDashboardDrugRoute('destroy', $drug->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this drug?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-rose-700 transition hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">Delete</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-module.detail-card title="Drug Information">
                <dl class="space-y-4">
                    <x-module.detail-field label="Drug Name" :value="$drug->drug_name" />
                    <x-module.detail-field label="Description">{{ $drug->description ?? 'N/A' }}</x-module.detail-field>
                    <x-module.detail-field label="Dosage" :value="$drug->dosage" />
                    <x-module.detail-field label="Dosage Form" :value="ucfirst($drug->dosage_form)" />
                    <x-module.detail-field label="Unit" :value="$drug->unit" />
                </dl>
            </x-module.detail-card>

            <x-module.detail-card title="Stock Status">
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Quantity On Hand</dt>
                        <dd class="mt-1 font-display text-3xl font-bold text-brand-600 dark:text-brand-400">{{ $drug->quantity_on_hand }}</dd>
                    </div>
                    <x-module.detail-field label="Reorder Point" :value="$drug->reorder_point" />
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Status</dt>
                        <dd class="mt-1">
                            <x-module.status-badge :variant="$drug->status_badge" :label="match($drug->status_badge) {
                                'active' => 'Active',
                                'expiring_soon' => 'Expiring Soon',
                                'expired' => 'Expired',
                                'low_stock' => 'Low Stock',
                                default => ucfirst($drug->status),
                            }" />
                        </dd>
                    </div>
                    <x-module.detail-field label="Days In Storage" :value="$drug->days_in_storage . ' days'" />
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Days Until Expiry</dt>
                        <dd class="mt-1 text-sm font-semibold @if($drug->days_until_expiry < 0) text-rose-600 @elseif($drug->days_until_expiry <= 180) text-amber-600 @else text-emerald-600 @endif">
                            {{ $drug->days_until_expiry }} days@if($drug->days_until_expiry < 0) (Expired)@endif
                        </dd>
                    </div>
                    <x-module.detail-field label="Last Issued Date" :value="$drug->last_issued_date ? $drug->last_issued_date->format('M d, Y') : 'Never'" />
                </dl>
            </x-module.detail-card>

            <x-module.detail-card title="Details">
                <dl class="space-y-4">
                    <x-module.detail-field label="Batch Number" :value="$drug->batch_number" />
                    <x-module.detail-field label="Expiry Date" :value="$drug->formatExpiry()" />
                    <x-module.detail-field label="Supplier" :value="$drug->supplier ?? 'N/A'" />
                    <x-module.detail-field label="Cost Per Unit" :value="$drug->cost_per_unit ? 'K' . number_format($drug->cost_per_unit, 2) : 'N/A'" />
                    <x-module.detail-field label="Storage Location" :value="$drug->storage_location ?? 'N/A'" />
                    <x-module.detail-field label="Level" :value="match($drug->level) {
                        'ndoh' => 'NDoH',
                        'lae_ams' => 'Lae AMS',
                        'modilon_hospital' => 'Modilon Hospital',
                        default => ucfirst($drug->level),
                    }" />
                    <x-module.detail-field label="Received Date" :value="$drug->formatReceivedDate()" />
                    <x-module.detail-field label="Created By" :value="$drug->createdBy->name . ' (' . $drug->created_at->format('M d, Y') . ')'" />
                    @if($drug->updated_by)
                        <x-module.detail-field label="Updated By" :value="$drug->updatedBy->name . ' (' . $drug->updated_at->format('M d, Y') . ')'" />
                    @endif
                </dl>
            </x-module.detail-card>
        </div>

        @if($drug->notes)
            <x-module.detail-card title="Notes" class="mt-6">
                <p class="text-sm text-ink-secondary dark:text-zinc-300">{{ $drug->notes }}</p>
            </x-module.detail-card>
        @endif
    </x-page-container>
</x-app-layout>
