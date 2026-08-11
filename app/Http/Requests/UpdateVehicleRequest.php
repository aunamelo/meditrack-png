<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('store_manager');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'registration' => [
                'required',
                'string',
                'max:50',
                Rule::unique('vehicles', 'registration')->ignore($vehicleId),
            ],
            'type' => ['required', Rule::in(['truck', 'van', 'ute'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Enter the vehicle name.',
            'registration.required' => 'Enter the registration plate or fleet number.',
            'registration.unique' => 'That registration is already registered.',
            'type.required' => 'Select a vehicle type.',
        ];
    }
}