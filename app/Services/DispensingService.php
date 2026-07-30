<?php

namespace App\Services;

use App\Models\DispensingRecord;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DispensingService
{
    /**
     * Dispense stock from a Modilon hospital lot to a patient.
     *
     * @param  array{drug_id: int, quantity_dispensed: int, prescription_ref?: string|null, notes?: string|null}  $data
     */
    public static function dispense(Patient $patient, User $pharmacist, array $data): DispensingRecord
    {
        if (! $patient->is_active) {
            throw new InvalidArgumentException('Cannot dispense to an inactive patient.');
        }

        return DB::transaction(function () use ($patient, $pharmacist, $data) {
            /** @var Drug $drug */
            $drug = Drug::query()->lockForUpdate()->findOrFail($data['drug_id']);

            if ($drug->level !== 'modilon_hospital') {
                throw new InvalidArgumentException('Only Modilon Hospital stock can be dispensed.');
            }

            if (! $drug->canBeDispensed()) {
                throw new InvalidArgumentException('This batch cannot be dispensed (expired or out of stock).');
            }

            $quantity = (int) $data['quantity_dispensed'];

            if ($quantity < 1 || $quantity > $drug->quantity_on_hand) {
                throw new InvalidArgumentException('Quantity exceeds available stock on this batch.');
            }

            $drug->updateQuantity($drug->quantity_on_hand - $quantity);
            $drug->update(['updated_by' => $pharmacist->id]);

            return DispensingRecord::create([
                'record_number' => DispensingRecord::generateRecordNumber(),
                'patient_id' => $patient->id,
                'drug_id' => $drug->id,
                'quantity_dispensed' => $quantity,
                'prescription_ref' => $data['prescription_ref'] ?? null,
                'prescription_date' => $data['prescription_date'] ?? null,
                'prescriber_name' => $data['prescriber_name'] ?? null,
                'prescribed_dose' => $data['prescribed_dose'] ?? null,
                'audit_date_checked' => (bool) ($data['audit_date_checked'] ?? false),
                'audit_prescriber_checked' => (bool) ($data['audit_prescriber_checked'] ?? false),
                'audit_drug_dose_checked' => (bool) ($data['audit_drug_dose_checked'] ?? false),
                'audit_contraindications_checked' => (bool) ($data['audit_contraindications_checked'] ?? false),
                'notes' => $data['notes'] ?? null,
                'dispensed_by' => $pharmacist->id,
                'dispensed_at' => now(),
            ]);
        });
    }
}
