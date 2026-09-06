<?php

namespace App\Http\Requests;

use App\Models\Drug;
use App\Models\HospitalOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ApproveHospitalOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('store_manager')
            && $this->route('hospitalOrder') instanceof HospitalOrder
            && $this->route('hospitalOrder')->canApprove();
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))->map(function (array $row) {
            $fulfill = filter_var($row['fulfill'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return [
                ...$row,
                'fulfill' => $fulfill,
                'source_drug_id' => $fulfill ? ($row['source_drug_id'] ?? null) : null,
                'quantity_approved' => $fulfill ? ($row['quantity_approved'] ?? null) : 0,
            ];
        })->all();

        $this->merge(['items' => $items]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:hospital_order_items,id'],
            'items.*.fulfill' => ['required', 'boolean'],
            'items.*.source_drug_id' => ['nullable', 'exists:drugs,id'],
            'items.*.quantity_approved' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var HospitalOrder $order */
            $order = $this->route('hospitalOrder');
            $order->loadMissing('items');
            $orderItemIds = $order->items->pluck('id')->map(fn ($id) => (int) $id)->all();
            $usedSourceIds = [];
            $fulfilledCount = 0;

            foreach ($this->input('items', []) as $index => $row) {
                $itemId = (int) ($row['id'] ?? 0);
                $item = $order->items->firstWhere('id', $itemId);
                $fulfill = (bool) ($row['fulfill'] ?? false);

                if (! $item || ! in_array($itemId, $orderItemIds, true)) {
                    $validator->errors()->add("items.{$index}.id", 'Invalid order line.');

                    continue;
                }

                if (! $fulfill) {
                    continue;
                }

                $fulfilledCount++;

                $drug = Drug::find($row['source_drug_id'] ?? null);
                if (! $drug) {
                    $validator->errors()->add("items.{$index}.source_drug_id", 'Select a Lae AMS batch for this medicine.');

                    continue;
                }

                $quantityApproved = (int) ($row['quantity_approved'] ?? 0);
                if ($quantityApproved < 1) {
                    $validator->errors()->add("items.{$index}.quantity_approved", 'Approved quantity must be at least 1 for fulfilled lines.');
                }

                if ($drug->level !== 'lae_ams') {
                    $validator->errors()->add("items.{$index}.source_drug_id", 'Only Lae AMS inventory can fulfill hospital orders.');
                }

                if ($drug->status === 'written_off' || $drug->is_expired) {
                    $validator->errors()->add("items.{$index}.source_drug_id", 'This batch is not available for road delivery.');
                }

                if (strcasecmp($drug->drug_name, $item->drug_name) !== 0
                    || strcasecmp((string) $drug->dosage, (string) $item->dosage) !== 0) {
                    $validator->errors()->add(
                        "items.{$index}.source_drug_id",
                        'Selected batch must match '.$item->displayLabel().'.'
                    );
                }

                if ($quantityApproved > $item->quantity_requested) {
                    $validator->errors()->add("items.{$index}.quantity_approved", 'Approved quantity cannot exceed the requested amount.');
                }

                if ($quantityApproved > $drug->quantity_on_hand) {
                    $validator->errors()->add(
                        "items.{$index}.quantity_approved",
                        "Insufficient Lae AMS stock. Only {$drug->quantity_on_hand} units available."
                    );
                }

                if (isset($usedSourceIds[$drug->id])) {
                    $validator->errors()->add("items.{$index}.source_drug_id", 'This Lae AMS batch is already assigned to another line.');
                }
                $usedSourceIds[$drug->id] = true;
            }

            $submittedIds = collect($this->input('items', []))->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (count($submittedIds) !== $order->items->count()
                || count(array_diff($orderItemIds, $submittedIds)) > 0) {
                $validator->errors()->add('items', 'Review every medicine line on this order (fulfill or skip).');
            }

            if ($fulfilledCount < 1) {
                $validator->errors()->add(
                    'items',
                    'Select at least one medicine that is in stock to approve. If nothing can be fulfilled, reject the order instead.'
                );
            }
        });
    }
}
