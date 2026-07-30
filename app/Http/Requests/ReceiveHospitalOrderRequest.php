<?php

namespace App\Http\Requests;

use App\Models\HospitalOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReceiveHospitalOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('pharmacy_manager');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'quantity_received' => ['required', 'integer', 'min:0'],
            'batch_verified' => ['accepted'],
            'expiry_verified' => ['accepted'],
            'condition' => ['required', Rule::in(['good', 'short_shipment', 'damaged', 'wrong_item', 'expired', 'other'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'batch_verified.accepted' => 'Confirm the batch number matches the delivery before receiving.',
            'expiry_verified.accepted' => 'Confirm the expiry date matches the delivery before receiving.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var HospitalOrder|null $order */
            $order = $this->route('hospitalOrder');
            if (! $order) {
                return;
            }

            $expected = (int) ($order->quantity_approved ?? $order->quantity_requested);
            $received = (int) $this->input('quantity_received');

            if ($received > $expected) {
                $validator->errors()->add('quantity_received', "Received quantity cannot exceed dispatched quantity ({$expected}).");
            }

            $condition = $this->input('condition');
            if ($received < $expected && $condition === 'good') {
                $validator->errors()->add('condition', 'Select a discrepancy condition when received quantity is less than dispatched.');
            }

            if ($received === $expected && in_array($condition, ['short_shipment'], true)) {
                $validator->errors()->add('condition', 'Short shipment requires a lower received quantity.');
            }
        });
    }
}
