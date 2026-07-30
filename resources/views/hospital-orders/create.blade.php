<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Hospital Supply</p>
            <h2 class="heading-page">New Hospital Order</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardHospitalOrderRoute('index')" label="Back to Hospital Orders" class="mb-6" />

        <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm text-slate-700 dark:border-brand-900 dark:bg-brand-950/30 dark:text-slate-300">
            Select a medicine to see <strong>average monthly consumption</strong>, <strong>days of stock</strong>, and a
            <strong>suggested request quantity</strong> (about {{ \App\Services\LmisService::HOSPITAL_MONTHS_OF_COVER }} months of cover). You can accept the suggestion or enter your own quantity.
        </div>

        <div class="module-form-shell">
            <form action="{{ getDashboardHospitalOrderRoute('store') }}" method="POST"
                  class="grid grid-cols-1 gap-6 md:grid-cols-2"
                  x-data="createHospitalOrderForm({
                      selectedKey: '',
                      drugName: @js(old('drug_name', '')),
                      dosage: @js(old('dosage', '')),
                      quantityRequested: @js(old('quantity_requested', '')),
                      notes: @js(old('notes', '')),
                      notesEdited: @js(filled(old('notes'))),
                      stockOptions: @js($stockOptions->values()),
                  })">
                @csrf
                @if($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="md:col-span-2">
                    <label for="stock_option" class="form-label">Medicine from Modilon stock status</label>
                    <select id="stock_option" x-model="selectedKey" @change="applyStockOption()" class="input-field">
                        <option value="">Manual entry / choose medicine…</option>
                        <template x-for="option in stockOptions" :key="option.key">
                            <option :value="option.key" x-text="`${option.label} · ${option.status_label} · suggest ${Number(option.suggested_quantity).toLocaleString()}`"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-muted">Prioritises stock-out and low-stock items. Leave blank to type a medicine name manually.</p>
                </div>

                <div class="md:col-span-2" x-show="selectedOption" x-cloak>
                    <div class="grid grid-cols-2 gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm md:grid-cols-4 dark:border-slate-700 dark:bg-slate-900/40">
                        <div>
                            <p class="text-xs uppercase text-gray-500">On hand</p>
                            <p class="font-semibold tabular-nums" x-text="selectedOption ? Number(selectedOption.stock_on_hand).toLocaleString() : '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-gray-500">AMC (3 mo)</p>
                            <p class="font-semibold tabular-nums" x-text="selectedOption ? Number(selectedOption.amc).toLocaleString() : '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-gray-500">Days of stock</p>
                            <p class="font-semibold tabular-nums" x-text="selectedOption && selectedOption.days_of_stock !== null ? Number(selectedOption.days_of_stock).toLocaleString() : '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-gray-500">Suggested</p>
                            <p class="font-semibold tabular-nums text-brand-700" x-text="selectedOption ? Number(selectedOption.suggested_quantity).toLocaleString() : '—'"></p>
                        </div>
                    </div>
                    <button type="button" class="mt-2 text-xs font-semibold text-brand-600 hover:underline" @click="useSuggestedQuantity()" x-show="selectedOption && Number(selectedOption.suggested_quantity) > 0">
                        Use suggested quantity
                    </button>
                </div>

                <div>
                    <label for="drug_name" class="form-label">Drug name *</label>
                    <input type="text" name="drug_name" id="drug_name" x-model="drugName" required class="input-field">
                </div>
                <div>
                    <label for="dosage" class="form-label">Dosage *</label>
                    <input type="text" name="dosage" id="dosage" x-model="dosage" required placeholder="e.g. 500mg" class="input-field">
                </div>
                <div>
                    <label for="quantity_requested" class="form-label">Quantity requested *</label>
                    <input type="number" name="quantity_requested" id="quantity_requested" x-model="quantityRequested" min="1" required class="input-field">
                </div>
                <div class="md:col-span-2">
                    <div class="mb-1 flex items-center justify-between">
                        <label for="notes" class="form-label">Notes</label>
                        <button type="button" x-show="notesEdited" x-cloak @click="notesEdited = false; refreshNotes()" class="text-xs font-medium text-brand-600 hover:underline">Regenerate from form</button>
                    </div>
                    <textarea name="notes" id="notes" rows="3" x-model="notes" @input="notesEdited = true" class="input-field"></textarea>
                    <p class="mt-1 text-xs text-muted">Auto-filled from your entries above. Edit to customize.</p>
                </div>
                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardHospitalOrderRoute('index') }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Submit to Lae AMS</button>
                </div>
            </form>
        </div>
    </x-page-container>

    <script>
        function createHospitalOrderForm(initial) {
            return {
                selectedKey: initial.selectedKey ?? '',
                drugName: initial.drugName ?? '',
                dosage: initial.dosage ?? '',
                quantityRequested: initial.quantityRequested ?? '',
                notes: initial.notes ?? '',
                notesEdited: initial.notesEdited ?? false,
                stockOptions: initial.stockOptions ?? [],
                get selectedOption() {
                    return this.stockOptions.find((option) => option.key === this.selectedKey) ?? null;
                },
                init() {
                    ['drugName', 'dosage', 'quantityRequested'].forEach((field) => {
                        this.$watch(field, () => this.refreshNotes());
                    });

                    if (!this.notesEdited) {
                        this.refreshNotes();
                    }
                },
                applyStockOption() {
                    const option = this.selectedOption;
                    if (!option) {
                        return;
                    }

                    this.drugName = option.drug_name;
                    this.dosage = option.dosage;
                    if (Number(option.suggested_quantity) > 0) {
                        this.quantityRequested = String(option.suggested_quantity);
                    }
                    this.refreshNotes();
                },
                useSuggestedQuantity() {
                    if (this.selectedOption && Number(this.selectedOption.suggested_quantity) > 0) {
                        this.quantityRequested = String(this.selectedOption.suggested_quantity);
                    }
                },
                buildNotes() {
                    const lines = [];
                    const quantity = Number(this.quantityRequested);
                    const option = this.selectedOption;

                    if (this.drugName && quantity > 0) {
                        lines.push(`Hospital replenishment request from Modilon Hospital for ${quantity.toLocaleString()} units of ${this.drugName}${this.dosage ? ` (${this.dosage})` : ''}.`);
                    } else if (this.drugName) {
                        lines.push(`Hospital replenishment request from Modilon Hospital for ${this.drugName}${this.dosage ? ` (${this.dosage})` : ''}.`);
                    } else {
                        lines.push('Hospital replenishment request from Modilon Hospital.');
                    }

                    if (option) {
                        lines.push(`LMIS suggestion: AMC ${Number(option.amc).toLocaleString()} / month, ${option.days_of_stock === null ? 'no DOS (no consumption history)' : `${Number(option.days_of_stock).toLocaleString()} days of stock`}, status ${option.status_label}, suggested ${Number(option.suggested_quantity).toLocaleString()} units (${option.months_of_cover} months cover).`);
                    }

                    lines.push('Submitted to Lae AMS regional warehouse for stock availability review.');

                    return lines.join('\n');
                },
                refreshNotes() {
                    if (!this.notesEdited) {
                        this.notes = this.buildNotes();
                    }
                },
            };
        }
    </script>
</x-app-layout>
