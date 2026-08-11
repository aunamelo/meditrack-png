<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Warehouse ops</p>
            <h2 class="heading-page">Edit Vehicle</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardVehicleRoute('show', $vehicle)" label="Back to vehicle" class="mb-6" />

        <div class="module-form-shell">
            <p class="mb-6 text-sm text-muted">
                Update fleet details for <strong>{{ $vehicle->displayLabel() }}</strong>. Inactive vehicles stay in the registry but cannot be selected for new shipments.
            </p>

            <form action="{{ getDashboardVehicleRoute('update', $vehicle) }}" method="POST">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 dark:border-rose-800 dark:bg-rose-950/30">
                        <ul class="list-inside list-disc text-sm text-red-700 dark:text-rose-300">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="name" class="form-label">Vehicle name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $vehicle->name) }}" required class="input-field">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="registration" class="form-label">Registration <span class="text-red-500">*</span></label>
                        <input type="text" name="registration" id="registration" value="{{ old('registration', $vehicle->registration) }}" required class="input-field">
                        @error('registration')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="type" class="form-label">Type <span class="text-red-500">*</span></label>
                        <select name="type" id="type" required class="input-field">
                            @foreach(['truck' => 'Truck', 'van' => 'Van', 'ute' => 'Ute'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', $vehicle->type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="input-field">{{ old('notes', $vehicle->notes) }}</textarea>
                        @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-ink dark:text-zinc-200">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-400 text-health-700 focus:ring-health-600" @checked(old('is_active', $vehicle->is_active))>
                            Active — available for hospital road shipments
                        </label>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Save changes</button>
                    <a href="{{ getDashboardVehicleRoute('show', $vehicle) }}" class="btn-module-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
