<?php

namespace Database\Seeders;

use App\Models\Medicine;
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

        $catalog = [
            ['name' => 'Paracetamol', 'dosage' => '500mg', 'dosage_form' => 'tablet', 'unit' => 'tablets', 'reorder_point' => 5000],
            ['name' => 'Amoxicillin', 'dosage' => '250mg', 'dosage_form' => 'capsule', 'unit' => 'capsules', 'reorder_point' => 3000],
            ['name' => 'Metformin', 'dosage' => '500mg', 'dosage_form' => 'tablet', 'unit' => 'tablets', 'reorder_point' => 2000],
            ['name' => 'Artemether/Lumefantrine', 'dosage' => '20/120mg', 'dosage_form' => 'tablet', 'unit' => 'tablets', 'reorder_point' => 4000],
            ['name' => 'ORS', 'dosage' => 'Standard', 'dosage_form' => 'other', 'unit' => 'sachets', 'reorder_point' => 10000],
        ];

        foreach ($catalog as $entry) {
            if ($entry['dosage_form'] === 'capsule') {
                $entry['dosage_form'] = 'tablet';
            }

            Medicine::firstOrCreate(
                [
                    'name' => $entry['name'],
                    'dosage' => $entry['dosage'],
                    'dosage_form' => $entry['dosage_form'],
                ],
                [
                    'unit' => $entry['unit'],
                    'reorder_point' => $entry['reorder_point'],
                    'is_active' => true,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]
            );
        }
    }
}
