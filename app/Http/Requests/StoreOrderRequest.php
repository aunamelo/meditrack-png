<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Only Procurement Officers can create orders.
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
            'quantity_ordered' => 'required|integer|min:1|max:999999',
            'supplier' => 'required|string|max:255',
            'order_date' => 'required|date|before_or_equal:today',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'supplier_invoice' => 'nullable|string|max:100',
            'invoice_amount' => 'nullable|numeric|min:0|max:999999.99',
            'source' => 'required|in:overseas,local,donation',
            'notes' => 'nullable|string',
        ];
    }
}
