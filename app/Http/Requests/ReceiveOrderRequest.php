<?php

namespace App\Http\Requests;

use App\Models\OrderItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReceiveOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:order_items,id',
            'items.*.quantity_received' => 'required|integer|min:0|max:999999',
            'received_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $order = $this->route('order');

            if (! $order) {
                return;
            }

            $order->loadMissing('items');

            foreach ($this->input('items', []) as $index => $row) {
                $item = $order->items->firstWhere('id', (int) ($row['id'] ?? 0));

                if (! $item instanceof OrderItem) {
                    $validator->errors()->add("items.{$index}.id", 'Invalid line item for this order.');

                    continue;
                }

                $qty = (int) ($row['quantity_received'] ?? 0);

                if ($qty > $item->remainingQuantity()) {
                    $validator->errors()->add(
                        "items.{$index}.quantity_received",
                        "Cannot receive more than {$item->remainingQuantity()} units for {$item->drug?->drug_name}."
                    );
                }
            }

            if (! collect($this->input('items', []))->contains(fn ($row) => (int) ($row['quantity_received'] ?? 0) > 0)) {
                $validator->errors()->add('items', 'Enter a received quantity for at least one line item.');
            }
        });
    }
}
