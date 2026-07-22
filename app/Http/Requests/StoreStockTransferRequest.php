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
            'drug_id' => 'required|exists:drugs,id',
            'quantity_sent' => 'required|integer|min:1|max:999999',
            'sent_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Ensure the drug is at NDoH level with sufficient stock.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $drug = Drug::find($this->input('drug_id'));

            if (! $drug) {
                return;
            }

            if ($drug->level !== 'ndoh') {
                $validator->errors()->add('drug_id', 'Only NDoH level drugs can be shipped to Lae AMS.');
            }

            if ($drug->status === 'written_off') {
                $validator->errors()->add('drug_id', 'This drug has been written off and cannot be transferred.');
            }

            if ($drug->is_expired) {
                $validator->errors()->add('drug_id', 'Expired drugs cannot be shipped.');
            }

            $quantitySent = (int) $this->input('quantity_sent');
            if ($quantitySent > $drug->quantity_on_hand) {
                $validator->errors()->add(
                    'quantity_sent',
                    "Insufficient stock. Only {$drug->quantity_on_hand} units available."
                );
            }
        });
    }
}
