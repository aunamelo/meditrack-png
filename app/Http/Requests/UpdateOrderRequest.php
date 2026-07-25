<?php

namespace App\Http\Requests;

use App\Models\Supplier;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return auth()->user()->hasRole('procurement_officer')
            && $order
            && $order->created_by === auth()->id()
            && $order->status === 'pending';
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\Order $order */
        $order = $this->route('order');

        return [
            'items' => ['required', 'array', 'min:1', 'max:25'],
            'items.*.medicine_id' => [
                'required',
                Rule::exists('medicines', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1', 'max:999999'],
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'expected_delivery_date' => ['nullable', 'date', 'after:'.$order->order_date->format('Y-m-d')],
            'notes' => 'nullable|string',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var \App\Models\Order $order */
            $order = $this->route('order');
            $supplier = Supplier::query()->find($this->input('supplier_id'));

            if (! $supplier || ! $order) {
                return;
            }

            $matchesSource = match ($order->source) {
                'overseas' => in_array($supplier->country, ['india', 'china'], true),
                'local' => $supplier->country === 'png',
                'donation' => $supplier->country === 'international',
                default => false,
            };

            if (! $matchesSource) {
                $validator->errors()->add(
                    'supplier_id',
                    'Overseas orders must use a registered India or China manufacturer; local and donation orders require a matching supplier type.'
                );
            }
        });
    }
}
