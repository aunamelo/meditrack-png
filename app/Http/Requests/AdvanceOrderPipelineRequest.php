<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdvanceOrderPipelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $order = $this->route('order');

        if (! $user || ! $order) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('procurement_officer') && $order->created_by === $user->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
