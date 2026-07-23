<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
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
            'items' => ['required', 'array', 'min:1', 'max:25'],
            'items.*.medicine_id' => [
                'required',
                Rule::exists('medicines', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1', 'max:999999'],
            'supplier' => 'required|string|max:255',
            'order_date' => 'required|date|before_or_equal:today',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'supplier_invoice' => 'nullable|string|max:100',
            'invoice_amount' => 'nullable|numeric|min:0|max:999999.99',
            'source' => 'required|in:overseas,local,donation',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one medicine line to the order.',
            'items.min' => 'Add at least one medicine line to the order.',
            'items.*.medicine_id.required' => 'Select a medicine for each line item.',
            'items.*.quantity_ordered.required' => 'Enter a quantity for each line item.',
        ];
    }
}
