<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Stock</p>
            <h2 class="heading-page">Enter New Drug</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardDrugRoute('index')" label="Back to Drugs" class="mb-6" />

        <div class="module-form-shell">

                    <!-- Form -->
                    <form action="{{ getDashboardDrugRoute('store') }}" method="POST"
                          x-data="{
                              costPerUnit: @js(old('cost_per_unit', '')),
                              quantityReceived: @js(old('quantity_received', '')),
                          }"
                          x-init="$watch('quantityReceived', () => $dispatch('currency-recalculate'))"
                          @pgk-per-unit-applied.window="costPerUnit = Number($event.detail.amount).toFixed(4)"
                          @pgk-total-applied.window="costPerUnit = (Number($event.detail.amount) / (Number(quantityReceived) || 1)).toFixed(4)">
                        @csrf

                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">There were some errors with your submission.</h3>
                                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Drug Name -->
                            <div>
                                <label for="drug_name" class="form-label">Drug Name <span class="text-red-500">*</span></label>
                                <input type="text" name="drug_name" id="drug_name" value="{{ old('drug_name') }}" required
                                    class="input-field"
                                    placeholder="e.g., Paracetamol">
                                @error('drug_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Batch Number -->
                            <div>
                                <label for="batch_number" class="form-label">Batch Number <span class="text-red-500">*</span></label>
                                <input type="text" name="batch_number" id="batch_number" value="{{ old('batch_number') }}" required
                                    class="input-field"
                                    placeholder="e.g., BATCH-2024-001">
                                @error('batch_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <label for="expiry_date" class="form-label">Expiry Date <span class="text-red-500">*</span></label>
                                <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" required min="{{ date('Y-m-d') }}"
                                    class="input-field">
                                @error('expiry_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quantity Received -->
                            <div>
                                <label for="quantity_received" class="form-label">Quantity Received <span class="text-red-500">*</span></label>
                                <input type="number" name="quantity_received" id="quantity_received" x-model="quantityReceived" required min="1"
                                    class="input-field"
                                    placeholder="e.g., 100">
                                @error('quantity_received')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Dosage -->
                            <div>
                                <label for="dosage" class="form-label">Dosage <span class="text-red-500">*</span></label>
                                <input type="text" name="dosage" id="dosage" value="{{ old('dosage') }}" required
                                    class="input-field"
                                    placeholder="e.g., 500mg">
                                @error('dosage')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Dosage Form -->
                            <div>
                                <label for="dosage_form" class="form-label">Dosage Form <span class="text-red-500">*</span></label>
                                <select name="dosage_form" id="dosage_form" required
                                    class="input-field">
                                    <option value="">Select form...</option>
                                    <option value="tablet" {{ old('dosage_form') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                                    <option value="injection" {{ old('dosage_form') == 'injection' ? 'selected' : '' }}>Injection</option>
                                    <option value="syrup" {{ old('dosage_form') == 'syrup' ? 'selected' : '' }}>Syrup</option>
                                    <option value="cream" {{ old('dosage_form') == 'cream' ? 'selected' : '' }}>Cream</option>
                                    <option value="ointment" {{ old('dosage_form') == 'ointment' ? 'selected' : '' }}>Ointment</option>
                                    <option value="other" {{ old('dosage_form') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('dosage_form')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Unit -->
                            <div>
                                <label for="unit" class="form-label">Unit <span class="text-red-500">*</span></label>
                                <input type="text" name="unit" id="unit" value="{{ old('unit') }}" required
                                    class="input-field"
                                    placeholder="e.g., tablets, ml, vials">
                                @error('unit')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Supplier -->
                            <div>
                                <label for="supplier" class="form-label">Supplier</label>
                                <input type="text" name="supplier" id="supplier" value="{{ old('supplier') }}"
                                    class="input-field"
                                    placeholder="e.g., Pfizer Pharmaceuticals">
                                @error('supplier')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cost Per Unit -->
                            <div>
                                <label for="cost_per_unit" class="form-label">Cost Per Unit (PGK)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">K</span>
                                    <input type="number" name="cost_per_unit" id="cost_per_unit" x-model="costPerUnit" step="0.0001" min="0"
                                        class="input-field pl-8"
                                        placeholder="e.g., 5.50">
                                </div>
                                @error('cost_per_unit')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-currency-converter
                                    quantity-alpine="quantityReceived"
                                    apply-event="pgk-total-applied"
                                    per-unit-event="pgk-per-unit-applied"
                                    auto-apply-target="per_unit"
                                    default-currency="USD"
                                />
                            </div>

                            <!-- Storage Location -->
                            <div>
                                <label for="storage_location" class="form-label">Storage Location</label>
                                <input type="text" name="storage_location" id="storage_location" value="{{ old('storage_location') }}"
                                    class="input-field"
                                    placeholder="e.g., Shelf A1">
                                @error('storage_location')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Reorder Point -->
                            <div>
                                <label for="reorder_point" class="form-label">Reorder Point</label>
                                <input type="number" name="reorder_point" id="reorder_point" value="{{ old('reorder_point') ?? 100 }}" min="1"
                                    class="input-field"
                                    placeholder="e.g., 100">
                                @error('reorder_point')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" rows="3"
                                    class="input-field"
                                    placeholder="Enter drug description...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="input-field"
                                    placeholder="Enter any additional notes...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="mt-6 flex items-center justify-end gap-4">
                            <a href="{{ getDashboardDrugRoute('index') }}" class="btn-module-secondary">Cancel</a>
                            <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Save Drug</button>
                        </div>
                    </form>
        </div>
    </x-page-container>
</x-app-layout>
