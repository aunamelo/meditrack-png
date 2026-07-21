<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    /**
     * Procurement Officers (own orders) or NDoH Admin can cancel.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        $order = $this->route('order');

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('procurement_officer')
            && $order->created_by === $user->id
            && $order->status === 'pending';
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:500',
        ];
    }
}
