<?php

namespace App\Http\Requests;

use App\Models\HospitalOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReceiveHospitalOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('pharmacy_manager');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
            'batch_verified' => ['accepted'],
            'expiry_verified' => ['accepted'],
            'condition' => ['required', Rule::in(['good', 'short_shipment', 'damaged', 'wrong_item', 'expired', 'other'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'batch_verified.accepted' => 'Confirm the batch numbers match the delivery before receiving.',
            'expiry_verified.accepted' => 'Confirm the expiry dates match the delivery before receiving.',
            'items.required' => 'Enter received quantities for each medicine on the delivery.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var HospitalOrder|null $order */
            $order = $this->route('hospitalOrder');
            if (! $order) {
                return;
            }

            $transfer = $order->stockTransfer()?->with('items')->first();
            if (! $transfer) {
                return;
            }

            $lines = $transfer->items;
            $submitted = collect($this->input('items', []))->keyBy('id');
            $totalSent = 0;
            $totalReceived = 0;

            if ($lines->isEmpty()) {
                // Legacy single-line transfer — allow quantity_received at top level via items[0].
                $first = $this->input('items.0.quantity_received', $this->input('quantity_received'));
                $totalSent = (int) $transfer->quantity_sent;
                $totalReceived = (int) $first;
            } else {
                foreach ($lines as $index => $line) {
                    $row = $submitted->get((string) $line->id) ?? $submitted->get($line->id);
                    if (! $row) {
                        $validator->errors()->add('items', 'Enter a received quantity for every medicine on this delivery.');

                        continue;
                    }

                    $sent = (int) $line->quantity_sent;
                    $received = (int) ($row['quantity_received'] ?? 0);
                    $totalSent += $sent;
                    $totalReceived += $received;

                    if ($received > $sent) {
                        $validator->errors()->add(
                            "items.{$index}.quantity_received",
                            "Received quantity cannot exceed dispatched quantity ({$sent})."
                        );
                    }
                }
            }

            $condition = $this->input('condition');
            if ($totalReceived < $totalSent && $condition === 'good') {
                $validator->errors()->add('condition', 'Select a discrepancy condition when received quantity is less than dispatched.');
            }

            if ($totalReceived === $totalSent && in_array($condition, ['short_shipment'], true)) {
                $validator->errors()->add('condition', 'Short shipment requires a lower received quantity.');
            }
        });
    }
}
