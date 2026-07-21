<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDrugRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only Procurement Officers can create drugs.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('procurement_officer');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'drug_name' => 'required|string|max:255',
            'batch_number' => 'required|string|max:100|unique:drugs,batch_number',
            'expiry_date' => 'required|date|after_or_equal:today',
            'quantity_received' => 'required|integer|min:1|max:999999',
            'description' => 'nullable|string',
            'dosage' => 'required|string|max:100',
            'dosage_form' => 'required|in:tablet,injection,syrup,cream,ointment,other',
            'unit' => 'required|string|max:50',
            'supplier' => 'nullable|string|max:255',
            'cost_per_unit' => 'nullable|numeric|min:0|max:999999.99',
            'storage_location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'reorder_point' => 'nullable|integer|min:1|max:999999',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'expiry_date.after_or_equal' => 'Expiry date must be today or in the future',
            'quantity_received.required' => 'Quantity received is required',
            'quantity_received.min' => 'Quantity received must be at least 1',
            'batch_number.unique' => 'This batch number already exists',
            'dosage_form.in' => 'Dosage form must be one of: tablet, injection, syrup, cream, ointment, or other',
            'reorder_point.min' => 'Reorder point must be at least 1',
        ];
    }
}
