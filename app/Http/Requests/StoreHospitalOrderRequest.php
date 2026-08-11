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
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.stock_key' => ['required', 'string', 'max:255'],
            'items.*.drug_name' => ['required', 'string', 'max:255'],
            'items.*.dosage' => ['required', 'string', 'max:100'],
            'items.*.quantity_requested' => ['required', 'integer', 'min:1', 'max:999999'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $options = LmisService::hospitalRequisitionOptions()->keyBy('key');
            $seenKeys = [];

            foreach ($this->input('items', []) as $index => $item) {
                $stockKey = (string) ($item['stock_key'] ?? '');
                $option = $options->get($stockKey);

                if (! $option) {
                    $validator->errors()->add(
                        "items.{$index}.stock_key",
                        'Select a medicine from Modilon stock status or the NDoH catalog.'
                    );

                    continue;
                }

                if (
                    strcasecmp((string) ($item['drug_name'] ?? ''), (string) $option['drug_name']) !== 0
                    || strcasecmp((string) ($item['dosage'] ?? ''), (string) $option['dosage']) !== 0
                ) {
                    $validator->errors()->add(
                        "items.{$index}.drug_name",
                        'Drug details must match the selected catalog or stock-status medicine.'
                    );
                }

                // Same medicine twice in one submission is confusing for Lae AMS review.
                if (isset($seenKeys[$stockKey])) {
                    $validator->errors()->add(
                        "items.{$index}.stock_key",
                        'This medicine is already on another line. Combine quantities on one line.'
                    );
                }
                $seenKeys[$stockKey] = true;
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one medicine request.',
            'items.min' => 'Add at least one medicine request.',
            'items.*.stock_key.required' => 'Select a medicine for each line.',
            'items.*.drug_name.required' => 'Select a medicine for each line.',
            'items.*.dosage.required' => 'Select a medicine for each line.',
            'items.*.quantity_requested.required' => 'Enter a quantity for each line.',
            'items.*.quantity_requested.min' => 'Quantity must be at least 1.',
        ];
    }
}
