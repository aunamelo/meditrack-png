<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Hospital Supply</p>
            <h2 class="heading-page">New Hospital Order</h2>
        </div>
    </x-slot>

    <x-page-container>
        <div class="surface-panel">
            <div class="p-6">
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
                        <div class="md:col-span-2 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div>
                        <label for="drug_name" class="mb-1 block text-sm font-medium">Drug name *</label>
                        <input type="text" name="drug_name" id="drug_name" x-model="drugName" required class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="dosage" class="mb-1 block text-sm font-medium">Dosage *</label>
                        <input type="text" name="dosage" id="dosage" x-model="dosage" required placeholder="e.g. 500mg" class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="quantity_requested" class="mb-1 block text-sm font-medium">Quantity requested *</label>
                        <input type="number" name="quantity_requested" id="quantity_requested" x-model="quantityRequested" min="1" required class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="md:col-span-2">
                        <div class="mb-1 flex items-center justify-between">
                            <label for="notes" class="block text-sm font-medium">Notes</label>
                            <button type="button" x-show="notesEdited" x-cloak @click="notesEdited = false; refreshNotes()" class="text-xs font-medium text-brand-600 hover:underline">Regenerate from form</button>
                        </div>
                        <textarea name="notes" id="notes" rows="3" x-model="notes" @input="notesEdited = true" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
                        <p class="mt-1 text-xs text-gray-500">Auto-filled from your entries above. Edit to customize.</p>
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-3">
                        <a href="{{ getDashboardHospitalOrderRoute('index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold">Cancel</a>
                        <button type="submit" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Submit to Lae AMS</button>
                    </div>
                </form>
            </div>
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
