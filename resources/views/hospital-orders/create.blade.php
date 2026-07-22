<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Hospital Supply</p>
            <h2 class="heading-page">New Hospital Order</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardHospitalOrderRoute('index')" label="Back to Hospital Orders" class="mb-6" />

        <div class="module-form-shell">
            <form action="{{ getDashboardHospitalOrderRoute('store') }}" method="POST"
                  class="grid grid-cols-1 gap-6 md:grid-cols-2"
                  x-data="createHospitalOrderForm({
                      drugName: @js(old('drug_name', '')),
                      dosage: @js(old('dosage', '')),
                      quantityRequested: @js(old('quantity_requested', '')),
                      notes: @js(old('notes', '')),
                      notesEdited: @js(filled(old('notes'))),
                  })">
                @csrf
                @if($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
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
                drugName: initial.drugName ?? '',
                dosage: initial.dosage ?? '',
                quantityRequested: initial.quantityRequested ?? '',
                notes: initial.notes ?? '',
                notesEdited: initial.notesEdited ?? false,
                init() {
                    ['drugName', 'dosage', 'quantityRequested'].forEach((field) => {
                        this.$watch(field, () => this.refreshNotes());
                    });

                    if (!this.notesEdited) {
                        this.refreshNotes();
                    }
                },
                buildNotes() {
                    const lines = [];
                    const quantity = Number(this.quantityRequested);

                    if (this.drugName && quantity > 0) {
                        lines.push(`Hospital replenishment request from Modilon Hospital for ${quantity.toLocaleString()} units of ${this.drugName}${this.dosage ? ` (${this.dosage})` : ''}.`);
                    } else if (this.drugName) {
                        lines.push(`Hospital replenishment request from Modilon Hospital for ${this.drugName}${this.dosage ? ` (${this.dosage})` : ''}.`);
                    } else {
                        lines.push('Hospital replenishment request from Modilon Hospital.');
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
