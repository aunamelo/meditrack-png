<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Warehouse ops</p>
            <h2 class="heading-page">Register Vehicle</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardVehicleRoute('index')" label="Back to vehicles" class="mb-6" />

        <div class="module-form-shell">
            <p class="mb-6 text-sm text-muted">
                Register a Lae AMS depot vehicle for Modilon Hospital road deliveries. Active vehicles can be selected when shipping an approved hospital order.
            </p>

            <form action="{{ getDashboardVehicleRoute('store') }}" method="POST">
                @csrf

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
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="input-field" placeholder="e.g., Lae AMS Cold Chain Truck 5">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="registration" class="form-label">Registration <span class="text-red-500">*</span></label>
                        <input type="text" name="registration" id="registration" value="{{ old('registration') }}" required class="input-field" placeholder="e.g., LAE-AMS-05">
                        @error('registration')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="type" class="form-label">Type <span class="text-red-500">*</span></label>
                        <select name="type" id="type" required class="input-field">
                            <option value="">Select type...</option>
                            @foreach(['truck' => 'Truck', 'van' => 'Van', 'ute' => 'Ute'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="input-field" placeholder="Optional fleet notes (cold chain, capacity, etc.)">{{ old('notes') }}</textarea>
                        @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Register vehicle</button>
                    <a href="{{ getDashboardVehicleRoute('index') }}" class="btn-module-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
