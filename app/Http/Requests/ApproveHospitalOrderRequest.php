<?php

namespace App\Http\Requests;

use App\Models\Drug;
use App\Models\HospitalOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ApproveHospitalOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('store_manager')
            && $this->route('hospitalOrder') instanceof HospitalOrder
            && $this->route('hospitalOrder')->canApprove();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source_drug_id' => ['required', 'exists:drugs,id'],
            'quantity_approved' => ['required', 'integer', 'min:1', 'max:999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var HospitalOrder $order */
            $order = $this->route('hospitalOrder');
            $drug = Drug::find($this->input('source_drug_id'));

            if (! $drug) {
                return;
            }

            if ($drug->level !== 'lae_ams') {
                $validator->errors()->add('source_drug_id', 'Only Lae AMS inventory can fulfill hospital orders.');
            }

            if ($drug->status === 'written_off' || $drug->is_expired) {
                $validator->errors()->add('source_drug_id', 'This batch is not available for road delivery.');
            }

            $quantityApproved = (int) $this->input('quantity_approved');

            if ($quantityApproved > $order->quantity_requested) {
                $validator->errors()->add('quantity_approved', 'Approved quantity cannot exceed the requested amount.');
            }

            if ($quantityApproved > $drug->quantity_on_hand) {
                $validator->errors()->add(
                    'quantity_approved',
                    "Insufficient Lae AMS stock. Only {$drug->quantity_on_hand} units available."
                );
            }
        });
    }
}
