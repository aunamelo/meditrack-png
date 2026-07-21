<?php

namespace App\Http\Requests;

use App\Models\HospitalOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDiscrepancyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('pharmacy_manager');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hospital_order_id' => ['nullable', 'exists:hospital_orders,id'],
            'stock_transfer_id' => ['nullable', 'exists:stock_transfers,id'],
            'issue_type' => ['required', 'in:short_shipment,damaged,wrong_item,expired,other'],
            'quantity_expected' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'quantity_received' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'description' => ['required', 'string', 'max:2000'],
        ];
    }
}
