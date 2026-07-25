<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicineRequest extends FormRequest
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
        /** @var \App\Models\Medicine $medicine */
        $medicine = $this->route('medicine');

        return [
            'name' => 'required|string|max:255',
            'dosage' => 'required|string|max:100',
            'dosage_form' => 'required|in:tablet,injection,syrup,cream,ointment,other',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'reorder_point' => 'nullable|integer|min:1|max:999999',
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where(fn ($query) => $query->where('is_active', true)->whereIn('country', ['india', 'china'])),
            ],
            'is_active' => 'sometimes|boolean',
        ];
    }
}
