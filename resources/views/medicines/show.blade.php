<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Procurement</p>
            <h2 class="heading-page">{{ $medicine->name }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <div class="module-actions-bar">
            <x-module.back-link :href="getDashboardMedicineRoute('index')" label="Back to catalog" />
            <div class="flex flex-wrap gap-3">
                <a href="{{ getDashboardMedicineRoute('edit', $medicine) }}" class="btn-module-secondary">Edit</a>
                @if(auth()->user()->hasRole('admin'))
                    <form action="{{ getDashboardMedicineRoute('destroy', $medicine) }}" method="POST" class="inline" onsubmit="return confirm('Remove or deactivate this catalog entry?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-rose-700 hover:bg-rose-100">Remove</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-module.detail-card title="Catalog entry">
                <dl class="space-y-4">
                    <x-module.detail-field label="Medicine" :value="$medicine->name" />
                    <x-module.detail-field label="Dosage" :value="$medicine->dosage" />
                    <x-module.detail-field label="Form" :value="$medicine->formLabel()" />
                    <x-module.detail-field label="Unit" :value="$medicine->unit" />
                    <x-module.detail-field label="Reorder point" :value="number_format($medicine->reorder_point)" />
                    <x-module.detail-field label="Status">
                        <x-module.status-badge :variant="$medicine->is_active ? 'green' : 'gray'" :label="$medicine->is_active ? 'Active' : 'Inactive'" />
                    </x-module.detail-field>
                    <x-module.detail-field label="Description">{{ $medicine->description ?? '—' }}</x-module.detail-field>
                </dl>
            </x-module.detail-card>

            <x-module.detail-card title="Audit">
                <dl class="space-y-4">
                    <x-module.detail-field label="Added by" :value="$medicine->createdBy->name ?? 'N/A'" />
                    <x-module.detail-field label="Added on" :value="$medicine->created_at->format('M d, Y')" />
                    @if($medicine->updatedBy)
                        <x-module.detail-field label="Last updated by" :value="$medicine->updatedBy->name" />
                        <x-module.detail-field label="Last updated" :value="$medicine->updated_at->format('M d, Y')" />
                    @endif
                </dl>
                <p class="mt-4 text-sm text-muted">Inventory batches linked to this medicine appear in Drug Inventory after orders are received.</p>
            </x-module.detail-card>
        </div>
    </x-page-container>
</x-app-layout>
