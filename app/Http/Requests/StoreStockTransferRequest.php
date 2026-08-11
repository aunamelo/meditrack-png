<?php

namespace App\Http\Requests;

use App\Models\Drug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStockTransferRequest extends FormRequest
{
    /**
     * Only Procurement Officers can ship stock from NDoH to Lae AMS.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('procurement_officer');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sent_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:30',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.quantity_sent' => 'required|integer|min:1|max:999999',
        ];
    }

    /**
     * Ensure each line is NDoH stock with enough on hand (including totals across lines).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $items = $this->input('items', []);
            $qtyByDrug = [];
            $seenDrugIds = [];

            foreach ($items as $index => $item) {
                $drugId = (int) ($item['drug_id'] ?? 0);
                $quantitySent = (int) ($item['quantity_sent'] ?? 0);
                $drug = Drug::find($drugId);

                if (! $drug) {
                    continue;
                }

                if (isset($seenDrugIds[$drugId])) {
                    $validator->errors()->add(
                        "items.{$index}.drug_id",
                        'This batch is already on another line. Combine quantities on one line.'
                    );
                }
                $seenDrugIds[$drugId] = true;

                if ($drug->level !== 'ndoh') {
                    $validator->errors()->add(
                        "items.{$index}.drug_id",
                        'Only NDoH level drugs can be shipped to Lae AMS.'
                    );
                }

                if ($drug->status === 'written_off') {
                    $validator->errors()->add(
                        "items.{$index}.drug_id",
                        'This drug has been written off and cannot be transferred.'
                    );
                }

                if ($drug->is_expired) {
                    $validator->errors()->add(
                        "items.{$index}.drug_id",
                        'Expired drugs cannot be shipped.'
                    );
                }

                $qtyByDrug[$drugId] = ($qtyByDrug[$drugId] ?? 0) + $quantitySent;

                if ($qtyByDrug[$drugId] > $drug->quantity_on_hand) {
                    $validator->errors()->add(
                        "items.{$index}.quantity_sent",
                        "Insufficient stock. Only {$drug->quantity_on_hand} units available for this batch."
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one batch to this delivery.',
            'items.min' => 'Add at least one batch to this delivery.',
            'items.*.drug_id.required' => 'Select a drug for each batch line.',
            'items.*.quantity_sent.required' => 'Enter a quantity for each batch line.',
            'items.*.quantity_sent.min' => 'Quantity must be at least 1.',
        ];
    }
}
