<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'procurement_officer']);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'dosage' => 'required|string|max:100',
            'dosage_form' => 'required|in:tablet,injection,syrup,cream,ointment,other',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'reorder_point' => 'nullable|integer|min:1|max:999999',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Enter the medicine name.',
            'dosage.required' => 'Enter the dosage strength.',
            'dosage_form.required' => 'Select a dosage form.',
            'unit.required' => 'Enter the unit of measure.',
        ];
    }
}
