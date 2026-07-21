<?php

namespace App\Http\Requests;

use App\Models\DiscrepancyReport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResolveDiscrepancyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('store_manager')
            && $this->route('discrepancy') instanceof DiscrepancyReport
            && $this->route('discrepancy')->isOpen();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resolution_notes' => ['required', 'string', 'max:2000'],
        ];
    }
}
