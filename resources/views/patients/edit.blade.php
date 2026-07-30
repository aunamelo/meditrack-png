<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Edit Patient</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardPatientRoute('show', $patient)" label="Back to patient" class="mb-6" />

        <div class="module-form-shell">
            <form action="{{ getDashboardPatientRoute('update', $patient) }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @csrf
                @method('PUT')
                @if($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="md:col-span-2 rounded-lg border border-line bg-canvas px-4 py-3 text-sm text-muted">
                    Patient #: <span class="font-semibold text-ink">{{ $patient->patient_number }}</span>
                </div>

                <div>
                    <label for="first_name" class="form-label">First name *</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $patient->first_name) }}" required class="input-field">
                </div>
                <div>
                    <label for="last_name" class="form-label">Last name *</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $patient->last_name) }}" required class="input-field">
                </div>
                <div>
                    <label for="date_of_birth" class="form-label">Date of birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', optional($patient->date_of_birth)->format('Y-m-d')) }}" class="input-field">
                </div>
                <div>
                    <label for="gender" class="form-label">Gender *</label>
                    <select name="gender" id="gender" required class="input-field">
                        @foreach(['unspecified' => 'Unspecified', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('gender', $patient->gender) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $patient->phone) }}" class="input-field">
                </div>
                <div>
                    <label for="facility" class="form-label">Facility</label>
                    <input type="text" name="facility" id="facility" value="{{ old('facility', $patient->facility) }}" class="input-field">
                </div>
                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm text-ink">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-line text-brand-600" @checked(old('is_active', $patient->is_active))>
                        Active patient (can receive dispensed medicines)
                    </label>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardPatientRoute('show', $patient) }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Save changes</button>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
