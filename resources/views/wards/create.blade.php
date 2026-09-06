<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Add Ward</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardWardRoute('index')" label="Back to wards" class="mb-6" />

        <div class="module-panel p-6">
            <form action="{{ getDashboardWardRoute('store') }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @csrf
                @if($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300" role="alert">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div>
                    <label for="name" class="form-label">Ward Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="input-field" placeholder="e.g., Medical Ward A">
                </div>

                <div>
                    <label for="type" class="form-label">Ward Type *</label>
                    <select name="type" id="type" required class="input-field">
                        <option value="">Select type...</option>
                        <option value="general" @selected(old('type') === 'general')">General Ward</option>
                        <option value="icu" @selected(old('type') === 'icu')">Intensive Care Unit</option>
                        <option value="pediatric" @selected(old('type') === 'pediatric')">Pediatric Ward</option>
                        <option value="maternity" @selected(old('type') === 'maternity')">Maternity Ward</option>
                        <option value="surgical" @selected(old('type') === 'surgical')">Surgical Ward</option>
                        <option value="emergency" @selected(old('type') === 'emergency')">Emergency Ward</option>
                        <option value="outpatient" @selected(old('type') === 'outpatient')">Outpatient Clinic</option>
                    </select>
                </div>

                <div>
                    <label for="location" class="form-label">Location</label>
                    <input type="text" name="location" id="location" value="{{ old('location') }}" class="input-field" placeholder="e.g., 2nd Floor, East Wing">
                </div>

                <div>
                    <label for="contact_person" class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person') }}" class="input-field" placeholder="e.g., Nurse in charge">
                </div>

                <div>
                    <label for="contact_phone" class="form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}" class="input-field" placeholder="e.g., +675 123 4567">
                </div>

                <div>
                    <label for="bed_capacity" class="form-label">Bed Capacity</label>
                    <input type="number" name="bed_capacity" id="bed_capacity" value="{{ old('bed_capacity') }}" min="0" class="input-field" placeholder="e.g., 20">
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardWardRoute('index') }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand">Create Ward</button>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
