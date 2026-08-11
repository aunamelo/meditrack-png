<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Procurement</p>
            <h2 class="heading-page">Add Medicine to Catalog</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardMedicineRoute('index')" label="Back to catalog" class="mb-6" />

        <div class="module-form-shell">
            <p class="mb-6 text-sm text-muted">Catalog entries describe essential medicines NDoH procures from registered manufacturers in India and China. Stock batches are created when goods are received against an approved order.</p>

            <form action="{{ getDashboardMedicineRoute('store') }}" method="POST"
                  x-data="medicineCatalogForm({
                      name: @js(old('name', '')),
                      dosage: @js(old('dosage', '')),
                      dosageForm: @js(old('dosage_form', '')),
                      supplierId: @js(old('supplier_id', '')),
                      unitCost: @js(old('unit_cost', '')),
                      currency: @js(old('currency', '')),
                      description: @js(old('description', '')),
                      descriptionEdited: @js(filled(old('description'))),
                      supplierOptions: @js($suppliers->map(fn ($supplier) => [
                          'id' => (string) $supplier->id,
                          'name' => $supplier->name,
                          'currency' => $supplier->procurementCurrency(),
                      ])->values()),
                      formLabels: @js(['tablet' => 'tablet', 'injection' => 'injection', 'syrup' => 'syrup', 'cream' => 'cream', 'ointment' => 'ointment', 'other' => 'other']),
                  })">
                @csrf

                @if ($errors->any())
                    <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4">
                        <ul class="list-inside list-disc text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="form-label">Medicine name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" x-model="name" value="{{ old('name') }}" required class="input-field" placeholder="e.g., Artemether/Lumefantrine">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="dosage" class="form-label">Dosage <span class="text-red-500">*</span></label>
                        <input type="text" name="dosage" id="dosage" x-model="dosage" value="{{ old('dosage') }}" required class="input-field" placeholder="e.g., 20/120 mg">
                        @error('dosage')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="dosage_form" class="form-label">Dosage form <span class="text-red-500">*</span></label>
                        <select name="dosage_form" id="dosage_form" x-model="dosageForm" required class="input-field">
                            <option value="">Select form...</option>
                            @foreach(['tablet' => 'Tablet', 'injection' => 'Injection', 'syrup' => 'Syrup', 'cream' => 'Cream', 'ointment' => 'Ointment', 'other' => 'Other'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('dosage_form') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('dosage_form')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="unit" class="form-label">Unit <span class="text-red-500">*</span></label>
                        <input type="text" name="unit" id="unit" value="{{ old('unit') }}" required class="input-field" placeholder="e.g., tablets, vials">
                        @error('unit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="reorder_point" class="form-label">Default reorder point</label>
                        <input type="number" name="reorder_point" id="reorder_point" value="{{ old('reorder_point', 100) }}" min="1" class="input-field">
                        @error('reorder_point')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="unit_cost" class="form-label">Unit cost <span class="text-red-500">*</span></label>
                        <input type="number" name="unit_cost" id="unit_cost" x-model="unitCost" value="{{ old('unit_cost') }}" step="0.0001" min="0" required class="input-field" placeholder="e.g. 2.40">
                        <p class="mt-1 text-xs text-muted">Supplier quote per unit in the currency below (INR for India, CNY for China).</p>
                        @error('unit_cost')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="currency" class="form-label">Currency <span class="text-red-500">*</span></label>
                        <select name="currency" id="currency" x-model="currency" required class="input-field">
                            <option value="">Select...</option>
                            <option value="INR">INR — Indian Rupee</option>
                            <option value="CNY">CNY — Chinese Yuan</option>
                            <option value="USD">USD — US Dollar</option>
                            <option value="PGK">PGK — Papua New Guinea Kina</option>
                        </select>
                        @error('currency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="supplier_id" class="form-label">Registered supplier (India / China) <span class="text-red-500">*</span></label>
                        <select name="supplier_id" id="supplier_id" x-model="supplierId" required class="input-field">
                            <option value="">Select manufacturer...</option>
                            @foreach($suppliers->groupBy('country') as $country => $group)
                                <optgroup label="{{ $group->first()->countryLabel() }}">
                                    @foreach($group as $supplier)
                                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}@if($supplier->headquarters) — {{ $supplier->headquarters }}@endif</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('supplier_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <div class="mb-1 flex items-center justify-between">
                            <label for="description" class="form-label">Description</label>
                            <button type="button" x-show="descriptionEdited" x-cloak @click="descriptionEdited = false; refreshDescription()" class="text-xs font-medium text-brand-600 hover:underline">Regenerate from form</button>
                        </div>
                        <textarea name="description" id="description" rows="3" x-model="description" @input="descriptionEdited = true" class="input-field"></textarea>
                        <p class="mt-1 text-xs text-muted">Auto-filled from medicine details and registered supplier. Edit to customize.</p>
                        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Save to catalog</button>
                    <a href="{{ getDashboardMedicineRoute('index') }}" class="btn-module-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </x-page-container>

    <script>
        function medicineCatalogForm(initial) {
            return {
                name: initial.name ?? '',
                dosage: initial.dosage ?? '',
                dosageForm: initial.dosageForm ?? '',
                supplierId: initial.supplierId ?? '',
                unitCost: initial.unitCost ?? '',
                currency: initial.currency ?? '',
                description: initial.description ?? '',
                descriptionEdited: initial.descriptionEdited ?? false,
                supplierOptions: initial.supplierOptions ?? [],
                formLabels: initial.formLabels ?? {},
                get supplierName() {
                    const option = this.supplierOptions.find((entry) => entry.id === String(this.supplierId));

                    return option ? option.name : '';
                },
                init() {
                    ['name', 'dosage', 'dosageForm', 'supplierId'].forEach((field) => {
                        this.$watch(field, () => this.refreshDescription());
                    });

                    this.$watch('supplierId', () => {
                        const option = this.supplierOptions.find((entry) => entry.id === String(this.supplierId));
                        if (option?.currency) {
                            this.currency = option.currency;
                        }
                    });

                    if (!this.descriptionEdited) {
                        this.refreshDescription();
                    }
                },
                buildDescription() {
                    const supplier = this.supplierName;

                    if (!supplier) {
                        return '';
                    }

                    const medicineName = String(this.name).trim();
                    const medicineDosage = String(this.dosage).trim();

                    if (medicineName && medicineDosage && this.dosageForm) {
                        const form = this.formLabels[this.dosageForm] ?? this.dosageForm;

                        return `Essential medicine (${medicineName} ${medicineDosage}, ${form}) — imported from ${supplier}.`;
                    }

                    return `Essential medicine — imported from ${supplier}.`;
                },
                refreshDescription() {
                    if (!this.descriptionEdited) {
                        this.description = this.buildDescription();
                    }
                },
            };
        }
    </script>
</x-app-layout>
