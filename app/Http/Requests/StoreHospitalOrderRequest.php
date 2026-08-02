<?php

namespace App\Http\Requests;

use App\Services\LmisService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreHospitalOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('pharmacy_manager');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'stock_key' => ['required', 'string', 'max:255'],
            'drug_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:100'],
            'quantity_requested' => ['required', 'integer', 'min:1', 'max:999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $option = LmisService::hospitalRequisitionOptions()
                ->firstWhere('key', $this->input('stock_key'));

            if (! $option) {
                $validator->errors()->add('stock_key', 'Select a medicine from Modilon stock status or the NDoH catalog.');

                return;
            }

            if (
                strcasecmp((string) $this->input('drug_name'), (string) $option['drug_name']) !== 0
                || strcasecmp((string) $this->input('dosage'), (string) $option['dosage']) !== 0
            ) {
                $validator->errors()->add('drug_name', 'Drug details must match the selected catalog or stock-status medicine.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'stock_key.required' => 'Select a medicine from the list.',
            'drug_name.required' => 'Select a medicine from the list.',
            'dosage.required' => 'Select a medicine from the list.',
        ];
    }
}
