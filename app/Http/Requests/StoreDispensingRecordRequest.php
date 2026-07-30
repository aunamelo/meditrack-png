<?php

namespace App\Http\Requests;

use App\Models\Drug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDispensingRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('pharmacist');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'drug_id' => [
                'required',
                'integer',
                Rule::exists('drugs', 'id')->where(fn ($q) => $q->where('level', 'modilon_hospital')),
            ],
            'quantity_dispensed' => ['required', 'integer', 'min:1', 'max:999999'],
            'prescription_ref' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $drugId = $this->input('drug_id');
            $qty = (int) $this->input('quantity_dispensed');

            if (! $drugId || $qty < 1) {
                return;
            }

            $drug = Drug::find($drugId);

            if (! $drug) {
                return;
            }

            if (! $drug->canBeDispensed()) {
                $validator->errors()->add('drug_id', 'This batch cannot be dispensed (expired or out of stock).');
            } elseif ($qty > $drug->quantity_on_hand) {
                $validator->errors()->add(
                    'quantity_dispensed',
                    "Only {$drug->quantity_on_hand} {$drug->unit} available on this batch."
                );
            }
        });
    }
}
