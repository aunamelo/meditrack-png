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
     *
     * Thirty-three essential medicines aligned to common PNG disease programmes
     * (malaria, TB, respiratory, diarrhoea, dengue, HIV, NCDs, anaemia, typhoid,
     * worms, leprosy, and emergency care).
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
            // 1. Malaria
            [
                'name' => 'Artemether',
                'dosage' => '80 mg/mL (1 mL)',
                'dosage_form' => 'injection',
                'unit' => 'ampoules',
                'reorder_point' => 600,
                'supplier' => 'Zhejiang Huahai Pharmaceutical Co., Ltd',
                'unit_cost' => 28.00,
                'currency' => 'CNY',
                'description' => 'Parenteral artemisinin derivative for severe malaria when oral ACT is not possible.',
            ],
            [
                'name' => 'Artemisinin-based Combination',
                'dosage' => '20/120 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 4000,
                'supplier' => 'Aurobindo Pharma Ltd',
                'unit_cost' => 18.50,
                'currency' => 'INR',
                'description' => 'Fixed-dose ACT (artemether/lumefantrine) for uncomplicated Plasmodium falciparum malaria.',
            ],
            [
                'name' => 'Quinine',
                'dosage' => '300 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 1500,
                'supplier' => "Dr. Reddy's Laboratories Ltd",
                'unit_cost' => 6.50,
                'currency' => 'INR',
                'description' => 'Oral quinine sulfate for malaria treatment when ACT is unavailable or contraindicated.',
            ],

            // 2. TB
            [
                'name' => 'Isoniazid',
                'dosage' => '300 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 3000,
                'supplier' => "Dr. Reddy's Laboratories Ltd",
                'unit_cost' => 1.80,
                'currency' => 'INR',
                'description' => 'First-line anti-TB drug for active tuberculosis and latent TB infection.',
            ],
            [
                'name' => 'Rifampicin',
                'dosage' => '450 mg',
                'dosage_form' => 'tablet',
                'unit' => 'capsules',
                'reorder_point' => 2500,
                'supplier' => 'Aurobindo Pharma Ltd',
                'unit_cost' => 4.20,
                'currency' => 'INR',
                'description' => 'Core bactericidal anti-TB drug used in standard first-line TB regimens.',
            ],
            [
                'name' => 'Pyrazinamide',
                'dosage' => '500 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 2500,
                'supplier' => 'Aurobindo Pharma Ltd',
                'unit_cost' => 2.40,
                'currency' => 'INR',
                'description' => 'Sterilising-phase anti-TB drug for intensive-phase tuberculosis treatment.',
            ],

            // 3. Respiratory
            [
                'name' => 'Amoxicillin',
                'dosage' => '500 mg',
                'dosage_form' => 'tablet',
                'unit' => 'capsules',
                'reorder_point' => 3000,
                'supplier' => 'Sun Pharmaceutical Industries Ltd',
                'unit_cost' => 3.20,
                'currency' => 'INR',
                'description' => 'First-line antibiotic for community-acquired pneumonia and lower respiratory tract infection.',
            ],
            [
                'name' => 'Ceftriaxone',
                'dosage' => '1 g',
                'dosage_form' => 'injection',
                'unit' => 'vials',
                'reorder_point' => 800,
                'supplier' => "Dr. Reddy's Laboratories Ltd",
                'unit_cost' => 42.00,
                'currency' => 'INR',
                'description' => 'Third-generation cephalosporin for severe pneumonia and hospitalised respiratory infection.',
            ],
            [
                'name' => 'Salbutamol',
                'dosage' => '2 mg/5 mL',
                'dosage_form' => 'syrup',
                'unit' => 'bottles (100 mL)',
                'reorder_point' => 1200,
                'supplier' => 'Sun Pharmaceutical Industries Ltd',
                'unit_cost' => 38.00,
                'currency' => 'INR',
                'description' => 'Short-acting bronchodilator syrup for asthma and acute bronchospasm.',
            ],

            // 4. Diarrhoea
            [
                'name' => 'ORS',
                'dosage' => '20.5 g sachet',
                'dosage_form' => 'other',
                'unit' => 'sachets',
                'reorder_point' => 10000,
                'supplier' => 'Sinopharm International Corporation',
                'unit_cost' => 1.60,
                'currency' => 'CNY',
                'description' => 'WHO low-osmolarity oral rehydration salts for acute watery diarrhoea and dehydration.',
            ],
            [
                'name' => 'Ciprofloxacin',
                'dosage' => '500 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 2000,
                'supplier' => 'Lupin Ltd',
                'unit_cost' => 2.80,
                'currency' => 'INR',
                'description' => 'Fluoroquinolone for dysentery and selected severe bacterial diarrhoea.',
            ],
            [
                'name' => 'Metronidazole',
                'dosage' => '400 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 2000,
                'supplier' => 'Lupin Ltd',
                'unit_cost' => 1.90,
                'currency' => 'INR',
                'description' => 'Antiprotozoal for amoebiasis, giardiasis, and anaerobic causes of diarrhoea.',
            ],

            // 5. Dengue
            [
                'name' => 'Paracetamol',
                'dosage' => '500 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 5000,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 0.85,
                'currency' => 'INR',
                'description' => 'Antipyretic and analgesic for dengue fever (avoid NSAIDs in suspected dengue).',
            ],
            [
                'name' => 'IV Fluids',
                'dosage' => '0.9% 500 mL',
                'dosage_form' => 'injection',
                'unit' => 'bags',
                'reorder_point' => 6000,
                'supplier' => 'Shanghai Pharmaceuticals Holding Co., Ltd',
                'unit_cost' => 4.25,
                'currency' => 'CNY',
                'description' => 'Normal saline IV infusion for dengue fluid resuscitation and maintenance.',
            ],

            // 6. HIV
            [
                'name' => 'Tenofovir',
                'dosage' => '300 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 2000,
                'supplier' => "Dr. Reddy's Laboratories Ltd",
                'unit_cost' => 8.50,
                'currency' => 'INR',
                'description' => 'NRTI backbone for first-line antiretroviral therapy (TDF component).',
            ],
            [
                'name' => 'Lamivudine',
                'dosage' => '150 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 2000,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 4.20,
                'currency' => 'INR',
                'description' => 'NRTI used in standard HIV combination antiretroviral regimens.',
            ],
            [
                'name' => 'Efavirenz',
                'dosage' => '600 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 1800,
                'supplier' => 'Aurobindo Pharma Ltd',
                'unit_cost' => 12.00,
                'currency' => 'INR',
                'description' => 'NNRTI for first-line HIV treatment in adults and adolescents.',
            ],

            // 7. Hypertension / diabetes
            [
                'name' => 'Amlodipine',
                'dosage' => '5 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 1800,
                'supplier' => 'Sun Pharmaceutical Industries Ltd',
                'unit_cost' => 1.05,
                'currency' => 'INR',
                'description' => 'Calcium-channel blocker for hypertension and cardiovascular risk reduction.',
            ],
            [
                'name' => 'Enalapril',
                'dosage' => '5 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 1500,
                'supplier' => 'Lupin Ltd',
                'unit_cost' => 1.20,
                'currency' => 'INR',
                'description' => 'ACE inhibitor for hypertension and heart failure with reduced ejection fraction.',
            ],
            [
                'name' => 'Metformin',
                'dosage' => '500 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 2000,
                'supplier' => "Dr. Reddy's Laboratories Ltd",
                'unit_cost' => 1.15,
                'currency' => 'INR',
                'description' => 'First-line oral antidiabetic for type 2 diabetes mellitus.',
            ],
            [
                'name' => 'Glibenclamide',
                'dosage' => '5 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 1200,
                'supplier' => "Dr. Reddy's Laboratories Ltd",
                'unit_cost' => 0.90,
                'currency' => 'INR',
                'description' => 'Sulfonylurea for type 2 diabetes when metformin alone is insufficient.',
            ],

            // 8. Anaemia
            [
                'name' => 'Ferrous Sulfate',
                'dosage' => '200 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 5000,
                'supplier' => 'Lupin Ltd',
                'unit_cost' => 0.75,
                'currency' => 'INR',
                'description' => 'Oral iron supplementation for iron-deficiency anaemia.',
            ],
            [
                'name' => 'Folic Acid',
                'dosage' => '5 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 4000,
                'supplier' => 'Lupin Ltd',
                'unit_cost' => 0.55,
                'currency' => 'INR',
                'description' => 'Folate supplementation for megaloblastic anaemia and antenatal care.',
            ],
            [
                'name' => 'Vitamin B12',
                'dosage' => '1 mg/mL (1 mL)',
                'dosage_form' => 'injection',
                'unit' => 'ampoules',
                'reorder_point' => 800,
                'supplier' => 'CSPC Pharmaceutical Group Ltd',
                'unit_cost' => 3.50,
                'currency' => 'CNY',
                'description' => 'Cyanocobalamin injection for B12-deficiency anaemia and malabsorption.',
            ],

            // 9. Typhoid
            [
                'name' => 'Chloramphenicol',
                'dosage' => '250 mg',
                'dosage_form' => 'tablet',
                'unit' => 'capsules',
                'reorder_point' => 1200,
                'supplier' => 'Lupin Ltd',
                'unit_cost' => 3.80,
                'currency' => 'INR',
                'description' => 'Alternative typhoid treatment where fluoroquinolone resistance is documented.',
            ],
            [
                'name' => 'Levofloxacin',
                'dosage' => '500 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 1500,
                'supplier' => "Dr. Reddy's Laboratories Ltd",
                'unit_cost' => 4.50,
                'currency' => 'INR',
                'description' => 'Fluoroquinolone for uncomplicated typhoid fever and enteric fever.',
            ],

            // 10. Worms
            [
                'name' => 'Albendazole',
                'dosage' => '400 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 5000,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 2.80,
                'currency' => 'INR',
                'description' => 'Broad-spectrum anthelmintic for soil-transmitted helminths and mass drug administration.',
            ],
            [
                'name' => 'Mebendazole',
                'dosage' => '500 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 4000,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 2.20,
                'currency' => 'INR',
                'description' => 'Anthelmintic for roundworm, whipworm, and hookworm infection.',
            ],
            [
                'name' => 'Praziquantel',
                'dosage' => '600 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 2000,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 6.00,
                'currency' => 'INR',
                'description' => 'Anthelmintic for schistosomiasis and tapeworm infection.',
            ],

            // 11. Leprosy
            [
                'name' => 'Rifampicin',
                'dosage' => '600 mg',
                'dosage_form' => 'tablet',
                'unit' => 'capsules',
                'reorder_point' => 800,
                'supplier' => 'Aurobindo Pharma Ltd',
                'unit_cost' => 5.50,
                'currency' => 'INR',
                'description' => 'Monthly supervised rifampicin dose for multibacillary leprosy (MDT blister packs).',
            ],
            [
                'name' => 'Dapsone',
                'dosage' => '100 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 1000,
                'supplier' => 'Lupin Ltd',
                'unit_cost' => 2.10,
                'currency' => 'INR',
                'description' => 'Daily dapsone for paucibacillary and multibacillary leprosy multidrug therapy.',
            ],

            // 12. Emergency
            [
                'name' => 'Epinephrine',
                'dosage' => '1 mg/mL (1 mL)',
                'dosage_form' => 'injection',
                'unit' => 'ampoules',
                'reorder_point' => 500,
                'supplier' => 'Zhejiang Huahai Pharmaceutical Co., Ltd',
                'unit_cost' => 2.20,
                'currency' => 'CNY',
                'description' => 'Emergency adrenaline for anaphylaxis, cardiac arrest, and severe asthma.',
            ],
            [
                'name' => 'Glucose',
                'dosage' => '50% 50 mL',
                'dosage_form' => 'injection',
                'unit' => 'ampoules',
                'reorder_point' => 600,
                'supplier' => 'Sinopharm International Corporation',
                'unit_cost' => 3.80,
                'currency' => 'CNY',
                'description' => 'Hypertonic dextrose injection for emergency hypoglycaemia and altered consciousness.',
            ],
        ];

        $catalogKeys = collect($catalog)->map(
            fn (array $entry) => "{$entry['name']}|{$entry['dosage']}|{$entry['dosage_form']}"
        );

        Medicine::query()->each(function (Medicine $medicine) use ($catalogKeys, $user) {
            $key = "{$medicine->name}|{$medicine->dosage}|{$medicine->dosage_form}";

            if (! $catalogKeys->contains($key)) {
                $medicine->update([
                    'is_active' => false,
                    'updated_by' => $user->id,
                ]);
            }
        });

        foreach ($catalog as $entry) {
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
                    'description' => $entry['description'].' Supplied via '.$entry['supplier'].'.',
                    'is_active' => true,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]
            );
        }
    }
}
