<?php

namespace App\Http\Requests;

use App\Models\HospitalOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RejectHospitalOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('store_manager')
            && $this->route('hospitalOrder') instanceof HospitalOrder
            && $this->route('hospitalOrder')->canReject();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
