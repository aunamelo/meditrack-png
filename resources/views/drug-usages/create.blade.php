<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Log Drug Usage</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardDrugUsageRoute('index')" label="Back to usage log" class="mb-6" />

        <div class="module-panel p-6">
            <form action="{{ getDashboardDrugUsageRoute('store') }}" method="POST" class="grid grid-cols-1 gap-6"
                  x-data="{
                      wardId: @js(old('ward_id', request('ward_id'))),
                      get wardDrugs() {
                          const ward = this.wards.find(w => w.id == this.wardId);
                          return ward ? ward.drugs : [];
                      }
                  }">
                @csrf
                @if($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300" role="alert">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div>
                    <label for="ward_id" class="form-label">Ward *</label>
                    <select name="ward_id" id="ward_id" x-model="wardId" required class="input-field">
                        <option value="">Select ward...</option>
                        @foreach($wards as $ward)
                            <option value="{{ $ward->id }}" @selected((string) old('ward_id', request('ward_id')) === (string) $ward->id)>
                                {{ $ward->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="drug_id" class="form-label">Drug *</label>
                    <select name="drug_id" id="drug_id" required class="input-field">
                        <option value="">Select drug...</option>
                        @foreach($wards as $ward)
                            @foreach($ward->wardStocks as $stock)
                                <option value="{{ $stock->drug->id }}" @selected((string) old('drug_id') === (string) $stock->drug->id) data-ward="{{ $ward->id }}">
                                    {{ $stock->drug->drug_name }} ({{ $stock->quantity_on_hand }} {{ $stock->drug->unit }} available)
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="patient_id" class="form-label">Patient</label>
                    <select name="patient_id" id="patient_id" class="input-field">
                        <option value="">No patient (general usage)</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" @selected((string) old('patient_id') === (string) $patient->id)>
                                {{ $patient->full_name }} ({{ $patient->patient_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="quantity_used" class="form-label">Quantity Used *</label>
                    <input type="number" name="quantity_used" id="quantity_used" value="{{ old('quantity_used') }}" min="1" required class="input-field" placeholder="Quantity">
                </div>

                <div>
                    <label for="usage_type" class="form-label">Usage Type *</label>
                    <select name="usage_type" id="usage_type" required class="input-field">
                        <option value="">Select type...</option>
                        <option value="administration" @selected(old('usage_type') === 'administration')">Administration</option>
                        <option value="wastage" @selected(old('usage_type') === 'wastage')">Wastage</option>
                        <option value="loss" @selected(old('usage_type') === 'loss')">Loss</option>
                        <option value="damage" @selected(old('usage_type') === 'damage')">Damage</option>
                        <option value="expired" @selected(old('usage_type') === 'expired')">Expired</option>
                        <option value="other" @selected(old('usage_type') === 'other')">Other</option>
                    </select>
                </div>

                <div>
                    <label for="usage_date" class="form-label">Usage Date *</label>
                    <input type="datetime-local" name="usage_date" id="usage_date" value="{{ old('usage_date', now()->format('Y-m-d\TH:i')) }}" required class="input-field">
                </div>

                <div>
                    <label for="recorded_by" class="form-label">Recorded By</label>
                    <input type="text" name="recorded_by" id="recorded_by" value="{{ old('recorded_by', auth()->user()->name) }}" class="input-field" placeholder="Nurse/Doctor name">
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="input-field" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardDrugUsageRoute('index') }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand">Log Usage</button>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
