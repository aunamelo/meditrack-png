<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReceiveOrderRequest extends FormRequest
{
    /**
     * Only NDoH Admin can receive orders.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quantity_received' => 'required|integer|min:1',
            'received_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Ensure received quantity does not exceed remaining ordered quantity.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $order = $this->route('order');

            if (! $order) {
                return;
            }

            $remaining = $order->quantity_ordered - ($order->quantity_received ?? 0);
            $received = (int) $this->input('quantity_received');

            if ($received > $remaining) {
                $validator->errors()->add(
                    'quantity_received',
                    "Quantity received cannot exceed remaining quantity ({$remaining})."
                );
            }
        });
    }
}
