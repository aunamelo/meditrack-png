<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Dispense Medicine</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardDispensingRoute('index')" label="Back to Dispensing" class="mb-6" />

        <div class="module-form-shell">
            <form action="{{ getDashboardDispensingRoute('store') }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2"
                  x-data="{
                      drugId: @js(old('drug_id', '')),
                      stock: @js($drugs->mapWithKeys(fn ($d) => [$d->id => ['qty' => $d->quantity_on_hand, 'unit' => $d->unit, 'label' => $d->drug_name.' · Batch '.$d->batch_number.' · '.$d->quantity_on_hand.' '.$d->unit]])->all()),
                      get selected() { return this.stock[this.drugId] || null; }
                  }">
                @csrf
                @if($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="md:col-span-2">
                    <label for="patient_id" class="form-label">Patient *</label>
                    <select name="patient_id" id="patient_id" required class="input-field">
                        <option value="">Select patient...</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" @selected((string) old('patient_id', $selectedPatientId) === (string) $patient->id)>
                                {{ $patient->patient_number }} — {{ $patient->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @if($patients->isEmpty())
                        <p class="mt-1 text-xs text-amber-700">No active patients. <a href="{{ getDashboardPatientRoute('create') }}" class="underline">Register one first</a>.</p>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label for="drug_id" class="form-label">Modilon stock batch *</label>
                    <select name="drug_id" id="drug_id" x-model="drugId" required class="input-field">
                        <option value="">Select batch...</option>
                        @foreach($drugs as $drug)
                            <option value="{{ $drug->id }}" @selected((string) old('drug_id') === (string) $drug->id)>
                                {{ $drug->drug_name }} ({{ $drug->dosage }}) · Batch {{ $drug->batch_number }} · {{ number_format($drug->quantity_on_hand) }} {{ $drug->unit }} · Exp {{ $drug->expiry_date->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted" x-show="selected" x-cloak x-text="selected ? ('Available: ' + selected.qty + ' ' + selected.unit) : ''"></p>
                    @if($drugs->isEmpty())
                        <p class="mt-1 text-xs text-amber-700">No dispensable Modilon stock. Check inventory or receive a hospital shipment first.</p>
                    @endif
                </div>

                <div>
                    <label for="quantity_dispensed" class="form-label">Quantity *</label>
                    <input type="number" name="quantity_dispensed" id="quantity_dispensed" value="{{ old('quantity_dispensed', 1) }}" min="1" required class="input-field">
                </div>
                <div>
                    <label for="prescription_ref" class="form-label">Prescription / Rx ref</label>
                    <input type="text" name="prescription_ref" id="prescription_ref" value="{{ old('prescription_ref') }}" placeholder="Optional clinic Rx number" class="input-field">
                </div>
                <div class="md:col-span-2">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="input-field">{{ old('notes') }}</textarea>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardDispensingRoute('index') }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider" @disabled($patients->isEmpty() || $drugs->isEmpty())>Confirm dispense</button>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
