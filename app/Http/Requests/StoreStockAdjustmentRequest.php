<?php

namespace App\Http\Requests;

use App\Models\Drug;
use App\Services\PortalNavigationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole([
            'admin',
            'procurement_officer',
            'store_manager',
            'pharmacy_manager',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'drug_id' => ['required', 'integer', 'exists:drugs,id'],
            'quantity_counted' => ['required', 'integer', 'min:0', 'max:999999'],
            'reason' => ['required', Rule::in([
                'physical_count',
                'damaged',
                'expired',
                'theft_loss',
                'found_stock',
                'correction',
                'other',
            ])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $meta = PortalNavigationService::currentRoleMeta();
            $level = $meta['inventory_level'] ?? null;
            $drugId = $this->input('drug_id');

            if (! $level || ! $drugId) {
                return;
            }

            $drug = Drug::query()->find($drugId);
            if ($drug && $drug->level !== $level) {
                $validator->errors()->add('drug_id', 'Selected batch is not at your facility.');
            }
        });
    }
}
