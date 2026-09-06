<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Issue Drugs to Ward</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardDrugIssuanceRoute('index')" label="Back to issuances" class="mb-6" />

        <div class="module-panel p-6">
            <form action="{{ getDashboardDrugIssuanceRoute('store') }}" method="POST" class="grid grid-cols-1 gap-6"
                  x-data="{
                      wardId: @js(old('ward_id', request('ward_id'))),
                      drugs: @js($drugs->map(fn ($d) => [
                          'id' => $d->id,
                          'name' => $d->drug_name,
                          'batch' => $d->batch_number,
                          'available' => $d->quantity_on_hand,
                          'unit' => $d->unit,
                          'expiry' => $d->expiry_date->format('d M Y'),
                          'label' => $d->drug_name . ' · Batch ' . $d->batch_number . ' · ' . $d->quantity_on_hand . ' ' . $d->unit,
                      ])->all()),
                      get selectedDrug() { return this.drugs.find(d => d.id == this.$el.querySelector('#drug_id')?.value) || null; }
                  }">
                @csrf
                @if($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300" role="alert">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div>
                    <label for="ward_id" class="form-label">Ward *</label>
                    <select name="ward_id" id="ward_id" required class="input-field">
                        <option value="">Select ward...</option>
                        @foreach($wards as $ward)
                            <option value="{{ $ward->id }}" @selected((string) old('ward_id', request('ward_id')) === (string) $ward->id)>
                                {{ $ward->name }} ({{ $ward->typeLabel() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="drug_id" class="form-label">Drug *</label>
                    <select name="drug_id" id="drug_id" x-model="$el.value" required class="input-field">
                        <option value="">Select drug...</option>
                        @foreach($drugs as $drug)
                            <option value="{{ $drug->id }}" @selected((string) old('drug_id') === (string) $drug->id)>
                                {{ $drug->drug_name }} · Batch {{ $drug->batch_number }} · {{ $drug->quantity_on_hand }} {{ $drug->unit }}
                            </option>
                        @endforeach
                    </select>
                    @if($drugs->isEmpty())
                        <p class="mt-1 text-xs text-amber-700">No drugs available in pharmacy stock.</p>
                    @endif
                </div>

                <template x-if="selectedDrug">
                    <div class="md:col-span-2 rounded-xl bg-canvas p-4 text-sm">
                        <p><span class="font-medium text-muted">Available:</span> <span x-text="selectedDrug.available"></span> <span x-text="selectedDrug.unit"></span></p>
                        <p><span class="font-medium text-muted">Batch:</span> <span x-text="selectedDrug.batch"></span></p>
                        <p><span class="font-medium text-muted">Expiry:</span> <span x-text="selectedDrug.expiry"></span></p>
                    </div>
                </template>

                <div>
                    <label for="quantity_issued" class="form-label">Quantity to Issue *</label>
                    <input type="number" name="quantity_issued" id="quantity_issued" value="{{ old('quantity_issued') }}" min="1" required class="input-field" placeholder="Quantity">
                </div>

                <div>
                    <label for="issuance_date" class="form-label">Issuance Date *</label>
                    <input type="date" name="issuance_date" id="issuance_date" value="{{ old('issuance_date', now()->format('Y-m-d')) }}" required class="input-field">
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="input-field" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardDrugIssuanceRoute('index') }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand">Issue Drugs</button>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
