<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Inventory</p>
            <h2 class="heading-page">New Stock Take</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardStockAdjustmentRoute('index')" label="Back to Stock Takes" class="mb-6" />

        <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm text-slate-700">
            Count the physical stock for a batch and post the result. The system quantity will be updated to match your count.
        </div>

        <div class="module-form-shell">
            <form action="{{ getDashboardStockAdjustmentRoute('store') }}" method="POST"
                  class="grid grid-cols-1 gap-6 md:grid-cols-2"
                  x-data="{
                      drugId: @js(old('drug_id', '')),
                      batches: @js($batches->map(fn ($b) => [
                          'id' => (string) $b->id,
                          'label' => $b->drug_name.' ('.$b->dosage.') · Batch '.$b->batch_number.' · On hand '.number_format($b->quantity_on_hand),
                          'on_hand' => (int) $b->quantity_on_hand,
                      ])->values()),
                      get selected() { return this.batches.find(b => b.id === String(this.drugId)) ?? null; }
                  }">
                @csrf
                @if($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @if(session('error'))
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">{{ session('error') }}</div>
                @endif

                <div class="md:col-span-2">
                    <label class="form-label">Batch *</label>
                    <select name="drug_id" x-model="drugId" required class="input-field">
                        <option value="">Select a batch…</option>
                        <template x-for="batch in batches" :key="batch.id">
                            <option :value="batch.id" x-text="batch.label"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-muted" x-show="selected" x-cloak>
                        System on hand: <strong x-text="selected ? Number(selected.on_hand).toLocaleString() : ''"></strong>
                    </p>
                </div>

                <div>
                    <label class="form-label">Quantity counted *</label>
                    <input type="number" name="quantity_counted" min="0" required value="{{ old('quantity_counted') }}" class="input-field">
                </div>

                <div>
                    <label class="form-label">Reason *</label>
                    <select name="reason" required class="input-field">
                        @foreach([
                            'physical_count' => 'Physical stock take',
                            'damaged' => 'Damaged',
                            'expired' => 'Expired / write-off',
                            'theft_loss' => 'Theft / loss',
                            'found_stock' => 'Found stock',
                            'correction' => 'Data correction',
                            'other' => 'Other',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected(old('reason', 'physical_count') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="input-field">{{ old('notes') }}</textarea>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardStockAdjustmentRoute('index') }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Post stock take</button>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
