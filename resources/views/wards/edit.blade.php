<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Edit Ward</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />
        <x-module.back-link :href="getDashboardWardRoute('show', $ward)" label="Back to ward" class="mb-6" />

        <div class="module-panel p-6">
            <form action="{{ getDashboardWardRoute('update', $ward) }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @csrf
                @method('PUT')
                @if($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300" role="alert">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div>
                    <label for="name" class="form-label">Ward Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $ward->name) }}" required class="input-field">
                </div>

                <div>
                    <label for="type" class="form-label">Ward Type *</label>
                    <select name="type" id="type" required class="input-field">
                        <option value="">Select type...</option>
                        <option value="general" @selected(old('type', $ward->type) === 'general')">General Ward</option>
                        <option value="icu" @selected(old('type', $ward->type) === 'icu')">Intensive Care Unit</option>
                        <option value="pediatric" @selected(old('type', $ward->type) === 'pediatric')">Pediatric Ward</option>
                        <option value="maternity" @selected(old('type', $ward->type) === 'maternity')">Maternity Ward</option>
                        <option value="surgical" @selected(old('type', $ward->type) === 'surgical')">Surgical Ward</option>
                        <option value="emergency" @selected(old('type', $ward->type) === 'emergency')">Emergency Ward</option>
                        <option value="outpatient" @selected(old('type', $ward->type) === 'outpatient')">Outpatient Clinic</option>
                    </select>
                </div>

                <div>
                    <label for="location" class="form-label">Location</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $ward->location) }}" class="input-field">
                </div>

                <div>
                    <label for="contact_person" class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $ward->contact_person) }}" class="input-field">
                </div>

                <div>
                    <label for="contact_phone" class="form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $ward->contact_phone) }}" class="input-field">
                </div>

                <div>
                    <label for="bed_capacity" class="form-label">Bed Capacity</label>
                    <input type="number" name="bed_capacity" id="bed_capacity" value="{{ old('bed_capacity', $ward->bed_capacity) }}" min="0" class="input-field">
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" @checked(old('is_active', $ward->is_active)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-ink">Active</span>
                    </label>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardWardRoute('show', $ward) }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand">Update Ward</button>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
