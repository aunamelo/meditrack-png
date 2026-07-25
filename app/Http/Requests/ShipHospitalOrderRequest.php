<?php

namespace App\Http\Requests;

use App\Models\HospitalOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipHospitalOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var HospitalOrder|null $order */
        $order = $this->route('hospitalOrder');

        return auth()->user()->hasRole('store_manager')
            && $order instanceof HospitalOrder
            && $order->canShip();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_id' => [
                'required',
                Rule::exists('vehicles', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->where('depot', 'lae_ams')),
            ],
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Select the vehicle carrying this delivery.',
            'vehicle_id.exists' => 'Select an active Lae AMS vehicle.',
        ];
    }
}
