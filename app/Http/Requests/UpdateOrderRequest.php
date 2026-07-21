<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Only Procurement Officers can update their own pending orders.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        $order = $this->route('order');

        return $user->hasRole('procurement_officer')
            && $order->created_by === $user->id
            && $order->status === 'pending';
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $order = $this->route('order');

        return [
            'quantity_ordered' => 'required|integer|min:1|max:999999',
            'supplier' => 'required|string|max:255',
            'expected_delivery_date' => 'nullable|date|after:'.$order->order_date->format('Y-m-d'),
            'notes' => 'nullable|string',
        ];
    }
}
