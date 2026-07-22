<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'items.*.drug_id' => [
                'required',
                Rule::exists('drugs', 'id')->where(fn ($query) => $query->where('level', 'ndoh')),
            ],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1', 'max:999999'],
            'supplier' => 'required|string|max:255',
            'expected_delivery_date' => ['nullable', 'date', 'after:'.$order->order_date->format('Y-m-d')],
            'notes' => 'nullable|string',
        ];
    }
}
