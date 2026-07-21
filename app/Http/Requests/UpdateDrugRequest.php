<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDrugRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Procurement Officer (ndoh), Pharmacy Manager (hospital), and NDoH Admin can update.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->hasRole('procurement_officer') || 
               $user->hasRole('pharmacy_manager') || 
               $user->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     * Only limited fields can be updated.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notes' => 'nullable|string',
            'reorder_point' => 'required|integer|min:1|max:999999',
            'storage_location' => 'nullable|string|max:100',
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
            'reorder_point.required' => 'Reorder point is required',
            'reorder_point.min' => 'Reorder point must be at least 1',
            'reorder_point.max' => 'Reorder point must not exceed 999,999',
        ];
    }
}
