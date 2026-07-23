<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Procurement</p>
            <h2 class="heading-page">Edit — {{ $medicine->name }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardMedicineRoute('show', $medicine)" :label="'Back to ' . $medicine->name" class="mb-6" />

        <div class="module-form-shell">
            <form action="{{ getDashboardMedicineRoute('update', $medicine) }}" method="POST">
                @csrf
                @method('PUT')

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
                        <input type="text" name="name" id="name" value="{{ old('name', $medicine->name) }}" required class="input-field">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="dosage" class="form-label">Dosage <span class="text-red-500">*</span></label>
                        <input type="text" name="dosage" id="dosage" value="{{ old('dosage', $medicine->dosage) }}" required class="input-field">
                        @error('dosage')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="dosage_form" class="form-label">Dosage form <span class="text-red-500">*</span></label>
                        <select name="dosage_form" id="dosage_form" required class="input-field">
                            @foreach(['tablet' => 'Tablet', 'injection' => 'Injection', 'syrup' => 'Syrup', 'cream' => 'Cream', 'ointment' => 'Ointment', 'other' => 'Other'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('dosage_form', $medicine->dosage_form) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('dosage_form')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="unit" class="form-label">Unit <span class="text-red-500">*</span></label>
                        <input type="text" name="unit" id="unit" value="{{ old('unit', $medicine->unit) }}" required class="input-field">
                        @error('unit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="reorder_point" class="form-label">Default reorder point</label>
                        <input type="number" name="reorder_point" id="reorder_point" value="{{ old('reorder_point', $medicine->reorder_point) }}" min="1" class="input-field">
                        @error('reorder_point')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    @if(auth()->user()->hasRole('admin'))
                        <div>
                            <label class="form-label">Catalog status</label>
                            <label class="mt-2 inline-flex items-center gap-2 text-sm text-ink">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $medicine->is_active)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                Active in catalog
                            </label>
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3" class="input-field">{{ old('description', $medicine->description) }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Save changes</button>
                    <a href="{{ getDashboardMedicineRoute('show', $medicine) }}" class="btn-module-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
