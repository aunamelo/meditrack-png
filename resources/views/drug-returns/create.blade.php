<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Return Drugs from Ward</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardDrugReturnRoute('index')" label="Back to returns" class="mb-6" />

        <div class="module-panel p-6">
            <form action="{{ getDashboardDrugReturnRoute('store') }}" method="POST" class="grid grid-cols-1 gap-6"
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
                    <label for="quantity_returned" class="form-label">Quantity to Return *</label>
                    <input type="number" name="quantity_returned" id="quantity_returned" value="{{ old('quantity_returned') }}" min="1" required class="input-field" placeholder="Quantity">
                </div>

                <div>
                    <label for="return_date" class="form-label">Return Date *</label>
                    <input type="date" name="return_date" id="return_date" value="{{ old('return_date', now()->format('Y-m-d')) }}" required class="input-field">
                </div>

                <div>
                    <label for="return_reason" class="form-label">Return Reason *</label>
                    <select name="return_reason" id="return_reason" required class="input-field">
                        <option value="">Select reason...</option>
                        <option value="excess" @selected(old('return_reason') === 'excess')">Excess Stock</option>
                        <option value="expired" @selected(old('return_reason') === 'expired')">Expired</option>
                        <option value="damaged" @selected(old('return_reason') === 'damaged')">Damaged</option>
                        <option value="wrong_batch" @selected(old('return_reason') === 'wrong_batch')">Wrong Batch</option>
                        <option value="patient_discharge" @selected(old('return_reason') === 'patient_discharge')">Patient Discharge</option>
                        <option value="other" @selected(old('return_reason') === 'other')">Other</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="input-field" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardDrugReturnRoute('index') }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand">Return Drugs</button>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
