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
            <p class="mb-6 text-sm text-muted">Catalog entries describe what NDoH can order. Stock batches with batch numbers and expiry dates are created when goods are received against an approved order.</p>

            <form action="{{ getDashboardMedicineRoute('store') }}" method="POST">
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
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="input-field" placeholder="e.g., Paracetamol">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="dosage" class="form-label">Dosage <span class="text-red-500">*</span></label>
                        <input type="text" name="dosage" id="dosage" value="{{ old('dosage') }}" required class="input-field" placeholder="e.g., 500mg">
                        @error('dosage')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="dosage_form" class="form-label">Dosage form <span class="text-red-500">*</span></label>
                        <select name="dosage_form" id="dosage_form" required class="input-field">
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

                    <div class="md:col-span-2">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3" class="input-field">{{ old('description') }}</textarea>
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
</x-app-layout>
