<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Seed the NDoH medicine catalog (procurement reference, not inventory).
     */
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            $this->command?->warn('MedicineSeeder skipped: no users found.');

            return;
        }

        $supplierIds = Supplier::query()->pluck('id', 'name');

        $catalog = [
            ['name' => 'Paracetamol', 'dosage' => '500mg', 'dosage_form' => 'tablet', 'unit' => 'tablets', 'reorder_point' => 5000, 'supplier' => 'Cipla Ltd', 'unit_cost' => 0.85, 'currency' => 'INR'],
            ['name' => 'Amoxicillin', 'dosage' => '250mg', 'dosage_form' => 'tablet', 'unit' => 'capsules', 'reorder_point' => 3000, 'supplier' => 'Sun Pharmaceutical Industries Ltd', 'unit_cost' => 2.40, 'currency' => 'INR'],
            ['name' => 'Metformin', 'dosage' => '500mg', 'dosage_form' => 'tablet', 'unit' => 'tablets', 'reorder_point' => 2000, 'supplier' => "Dr. Reddy's Laboratories Ltd", 'unit_cost' => 1.15, 'currency' => 'INR'],
            ['name' => 'Artemether/Lumefantrine', 'dosage' => '20/120mg', 'dosage_form' => 'tablet', 'unit' => 'tablets', 'reorder_point' => 4000, 'supplier' => 'Aurobindo Pharma Ltd', 'unit_cost' => 18.50, 'currency' => 'INR'],
            ['name' => 'ORS', 'dosage' => 'Standard', 'dosage_form' => 'other', 'unit' => 'sachets', 'reorder_point' => 10000, 'supplier' => 'Sinopharm International Corporation', 'unit_cost' => 1.60, 'currency' => 'CNY'],
            ['name' => 'Normal Saline', 'dosage' => '0.9%', 'dosage_form' => 'injection', 'unit' => 'bags', 'reorder_point' => 6000, 'supplier' => 'Shanghai Pharmaceuticals Holding Co., Ltd', 'unit_cost' => 4.25, 'currency' => 'CNY'],
        ];

        foreach ($catalog as $entry) {
            if ($entry['dosage_form'] === 'capsule') {
                $entry['dosage_form'] = 'tablet';
            }

            $supplierId = $supplierIds[$entry['supplier']] ?? null;

            Medicine::updateOrCreate(
                [
                    'name' => $entry['name'],
                    'dosage' => $entry['dosage'],
                    'dosage_form' => $entry['dosage_form'],
                ],
                [
                    'unit' => $entry['unit'],
                    'reorder_point' => $entry['reorder_point'],
                    'unit_cost' => $entry['unit_cost'],
                    'currency' => $entry['currency'],
                    'supplier_id' => $supplierId,
                    'description' => 'Essential medicine — imported from '.$entry['supplier'].'.',
                    'is_active' => true,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]
            );
        }
    }
}
