<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Modilon Pharmacy</p>
            <h2 class="heading-page">Dispense Medicine</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardDispensingRoute('index')" label="Back to Dispensing" class="mb-6" />

        <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm text-slate-700">
            Complete a prescription audit (date, prescriber, drug/dose, contraindications) before dispensing. Stock is reduced immediately when you confirm.
        </div>

        <div class="module-form-shell">
            <form action="{{ getDashboardDispensingRoute('store') }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2"
                  x-data="{
                      drugId: @js(old('drug_id', '')),
                      stock: @js($drugs->mapWithKeys(fn ($d) => [$d->id => [
                          'qty' => $d->quantity_on_hand,
                          'unit' => $d->unit,
                          'batch' => $d->batch_number,
                          'expiry' => $d->expiry_date->format('d M Y'),
                          'label' => $d->drug_name.' · Batch '.$d->batch_number.' · '.$d->quantity_on_hand.' '.$d->unit,
                      ]])->all()),
                      patients: @js($patients->map(fn ($p) => [
                          'id' => $p->id,
                          'patient_number' => $p->patient_number,
                          'full_name' => $p->full_name,
                          'date_of_birth' => $p->date_of_birth ? $p->date_of_birth->format('d M Y') : null,
                          'gender' => $p->genderLabel(),
                          'ward_clinic' => $p->ward_clinic,
                      ])->keyBy('id')->all()),
                      get selected() { return this.stock[this.drugId] || null; },
                      get selectedPatient() { return this.patients[this.$el.querySelector('#patient_id').value] || null; },
                      updatePatientFields() {
                          const patient = this.selectedPatient;
                          if (patient) {
                              this.$el.querySelector('#patient_dob').value = patient.date_of_birth || '';
                              this.$el.querySelector('#patient_gender').value = patient.gender || '';
                              this.$el.querySelector('#patient_number').value = patient.patient_number || '';
                              this.$el.querySelector('#patient_ward').value = patient.ward_clinic || '';
                          } else {
                              this.$el.querySelector('#patient_dob').value = '';
                              this.$el.querySelector('#patient_gender').value = '';
                              this.$el.querySelector('#patient_number').value = '';
                              this.$el.querySelector('#patient_ward').value = '';
                          }
                      }
                  }"
                  x-init="updatePatientFields()">
                @csrf
                @if($errors->any())
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300" role="alert">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="md:col-span-2 rounded-xl border border-line p-4">
                    <h3 class="mb-3 text-sm font-semibold text-ink">Patient Details</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="patient_id" class="form-label">Patient *</label>
                            <select name="patient_id" id="patient_id" x-model="$el.value" @change="updatePatientFields()" required class="input-field">
                                <option value="">Select patient...</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" @selected((string) old('patient_id', $selectedPatientId) === (string) $patient->id)>
                                        {{ $patient->patient_number }} — {{ $patient->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($patients->isEmpty())
                                <p class="mt-1 text-xs text-amber-700">No active patients. <a href="{{ getDashboardPatientRoute('create') }}" class="underline">Register one first</a>.</p>
                            @endif
                        </div>
                        <div>
                            <label for="patient_dob" class="form-label">Age / Date of Birth</label>
                            <input type="text" id="patient_dob" disabled class="input-field bg-gray-50" placeholder="Auto-filled from patient record">
                        </div>
                        <div>
                            <label for="patient_gender" class="form-label">Sex</label>
                            <input type="text" id="patient_gender" disabled class="input-field bg-gray-50" placeholder="Auto-filled from patient record">
                        </div>
                        <div>
                            <label for="patient_number" class="form-label">Hospital Number</label>
                            <input type="text" id="patient_number" disabled class="input-field bg-gray-50" placeholder="Auto-filled from patient record">
                        </div>
                        <div>
                            <label for="patient_ward" class="form-label">Ward / Clinic</label>
                            <input type="text" id="patient_ward" disabled class="input-field bg-gray-50" placeholder="Auto-filled from patient record">
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 rounded-xl border border-line p-4">
                    <h3 class="mb-3 text-sm font-semibold text-ink">Prescription Information</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="prescription_date" class="form-label">Date of Prescription *</label>
                            <input type="date" name="prescription_date" id="prescription_date" value="{{ old('prescription_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="input-field">
                        </div>
                        <div>
                            <label for="diagnosis" class="form-label">Diagnosis / Reason for Treatment</label>
                            <input type="text" name="diagnosis" id="diagnosis" value="{{ old('diagnosis') }}" placeholder="e.g., Hypertension, Infection" class="input-field">
                        </div>
                        <div class="md:col-span-2">
                            <label for="drug_id" class="form-label">Medicine Name (Modilon stock batch) *</label>
                            <select name="drug_id" id="drug_id" x-model="drugId" required class="input-field">
                                <option value="">Select batch...</option>
                                @foreach($drugs as $drug)
                                    <option value="{{ $drug->id }}" @selected((string) old('drug_id') === (string) $drug->id)>
                                        {{ $drug->drug_name }} ({{ $drug->dosage }}) · Batch {{ $drug->batch_number }} · {{ number_format($drug->quantity_on_hand) }} {{ $drug->unit }} · Exp {{ $drug->expiry_date->format('d M Y') }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-2">
                                <x-qr-scanner title="Scan batch QR" hint="Scan a batch label to select the matching Modilon stock batch." />
                            </div>
                            <div class="mt-2 rounded-lg border border-line bg-canvas p-3 text-xs text-muted" x-show="selected" x-cloak>
                                <p>Available: <strong class="text-ink" x-text="selected ? (selected.qty + ' ' + selected.unit) : ''"></strong></p>
                                <p>Batch: <strong class="text-ink" x-text="selected ? selected.batch : ''"></strong></p>
                                <p>Expiry: <strong class="text-ink" x-text="selected ? selected.expiry : ''"></strong></p>
                            </div>
                            @if($drugs->isEmpty())
                                <p class="mt-1 text-xs text-amber-700">No dispensable Modilon stock. Ask the Pharmacy Manager to receive a hospital shipment.</p>
                            @endif
                        </div>
                        <div>
                            <label for="dosage_form" class="form-label">Dosage Form</label>
                            <input type="text" name="dosage_form" id="dosage_form" value="{{ old('dosage_form') }}" placeholder="e.g., Tablet, Capsule, Syrup, Injection" class="input-field">
                        </div>
                        <div>
                            <label for="strength" class="form-label">Strength</label>
                            <input type="text" name="strength" id="strength" value="{{ old('strength') }}" placeholder="e.g., 250 mg, 500 mg" class="input-field">
                        </div>
                        <div>
                            <label for="quantity_to_dispense" class="form-label">Quantity to Dispense</label>
                            <input type="number" name="quantity_to_dispense" id="quantity_to_dispense" value="{{ old('quantity_to_dispense') }}" min="1" placeholder="e.g., 30" class="input-field">
                        </div>
                        <div>
                            <label for="quantity_dispensed" class="form-label">Quantity Dispensed *</label>
                            <input type="number" name="quantity_dispensed" id="quantity_dispensed" value="{{ old('quantity_dispensed', 1) }}" min="1" required class="input-field">
                        </div>
                        <div class="md:col-span-2">
                            <label for="directions_for_use" class="form-label">Directions for Use</label>
                            <textarea name="directions_for_use" id="directions_for_use" rows="2" class="input-field" placeholder="e.g., Take 1 tablet twice daily with food for 5 days">{{ old('directions_for_use') }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label for="prescribed_dose" class="form-label">Prescribed Dose *</label>
                            <input type="text" name="prescribed_dose" id="prescribed_dose" value="{{ old('prescribed_dose') }}" required placeholder="e.g. 500mg TDS x 5 days" class="input-field">
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 rounded-xl border border-line p-4">
                    <h3 class="mb-3 text-sm font-semibold text-ink">Prescriber Details</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="prescriber_name" class="form-label">Doctor's Name *</label>
                            <input type="text" name="prescriber_name" id="prescriber_name" value="{{ old('prescriber_name') }}" required placeholder="Doctor / clinician name" class="input-field">
                        </div>
                        <div>
                            <label for="prescriber_designation" class="form-label">Designation</label>
                            <input type="text" name="prescriber_designation" id="prescriber_designation" value="{{ old('prescriber_designation') }}" placeholder="e.g., Medical Officer, Specialist" class="input-field">
                        </div>
                        <div>
                            <label for="prescriber_signature" class="form-label">Signature</label>
                            <input type="text" name="prescriber_signature" id="prescriber_signature" value="{{ old('prescriber_signature') }}" placeholder="Doctor's signature" class="input-field">
                        </div>
                        <div class="md:col-span-3">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="hospital_stamp" value="1" @checked(old('hospital_stamp')) class="rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                                <span class="text-sm text-ink">Hospital stamp (for authentication)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 rounded-xl border border-line p-4">
                    <h3 class="mb-3 text-sm font-semibold text-ink">Pharmacy / Dispensing Section</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="pharmacist_signature" class="form-label">Pharmacist's Initials or Signature</label>
                            <input type="text" name="pharmacist_signature" id="pharmacist_signature" value="{{ old('pharmacist_signature') }}" placeholder="Pharmacist signature" class="input-field">
                        </div>
                        <div>
                            <label for="prescription_ref" class="form-label">Prescription / Rx Ref</label>
                            <input type="text" name="prescription_ref" id="prescription_ref" value="{{ old('prescription_ref') }}" placeholder="Optional clinic Rx number" class="input-field">
                        </div>
                        <div class="md:col-span-2">
                            <label for="dispensing_notes" class="form-label">Notes</label>
                            <textarea name="dispensing_notes" id="dispensing_notes" rows="2" class="input-field" placeholder="e.g., Substitutions, stock issues, counseling points">{{ old('dispensing_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 rounded-xl border border-line p-4">
                    <h3 class="mb-3 text-sm font-semibold text-ink">Additional Notes</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="allergies_contraindications" class="form-label">Allergies / Contraindications</label>
                            <textarea name="allergies_contraindications" id="allergies_contraindications" rows="2" class="input-field" placeholder="Known allergies or contraindications">{{ old('allergies_contraindications') }}</textarea>
                        </div>
                        <div>
                            <label for="repeat_instructions" class="form-label">Repeat Instructions</label>
                            <textarea name="repeat_instructions" id="repeat_instructions" rows="2" class="input-field" placeholder="Instructions if refill is allowed">{{ old('repeat_instructions') }}</textarea>
                        </div>
                        <div>
                            <label for="follow_up_instructions" class="form-label">Follow-up Instructions</label>
                            <textarea name="follow_up_instructions" id="follow_up_instructions" rows="2" class="input-field" placeholder="Follow-up instructions for the patient">{{ old('follow_up_instructions') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 rounded-xl border border-line p-4">
                    <h3 class="mb-3 text-sm font-semibold text-ink">Prescription Audit *</h3>
                    <div class="mt-4 space-y-2 text-sm text-ink">
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="audit_date_checked" value="1" @checked(old('audit_date_checked')) required class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                            <span>I checked the prescription date is valid.</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="audit_prescriber_checked" value="1" @checked(old('audit_prescriber_checked')) required class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                            <span>I verified the prescriber details.</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="audit_drug_dose_checked" value="1" @checked(old('audit_drug_dose_checked')) required class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                            <span>Drug and dose match the prescription and selected batch.</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="audit_contraindications_checked" value="1" @checked(old('audit_contraindications_checked')) required class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-600">
                            <span>I checked for contraindications / allergies with the patient or chart.</span>
                        </label>
                    </div>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardDispensingRoute('index') }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider" @disabled($patients->isEmpty() || $drugs->isEmpty())>Confirm dispense</button>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
