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
     * Entries mirror common PNG essential-medicine procurement lines
     * (malaria, maternal, antibiotics, fluids, NCDs) with realistic strengths.
     */
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            $this->command?->warn('MedicineSeeder skipped: no users found.');

            return;
        }

        $supplierIds = Supplier::query()->pluck('id', 'name');

        // Retire vague / compact-dosage legacy rows from earlier seeds.
        $legacyKeys = [
            ['ORS', 'Standard', 'other'],
            ['Normal Saline', '0.9%', 'injection'],
            ['Paracetamol', '500mg', 'tablet'],
            ['Amoxicillin', '250mg', 'tablet'],
            ['Metformin', '500mg', 'tablet'],
            ['Artemether/Lumefantrine', '20/120mg', 'tablet'],
        ];

        foreach ($legacyKeys as [$name, $dosage, $dosageForm]) {
            Medicine::query()
                ->where('name', $name)
                ->where('dosage', $dosage)
                ->where('dosage_form', $dosageForm)
                ->update([
                    'is_active' => false,
                    'updated_by' => $user->id,
                ]);
        }

        $catalog = [
            // Analgesics / antipyretics
            [
                'name' => 'Paracetamol',
                'dosage' => '500 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 5000,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 0.85,
                'currency' => 'INR',
                'description' => 'Oral analgesic/antipyretic tablets for fever and mild–moderate pain (adult dosing).',
            ],
            [
                'name' => 'Paracetamol',
                'dosage' => '120 mg/5 mL',
                'dosage_form' => 'syrup',
                'unit' => 'bottles (100 mL)',
                'reorder_point' => 1500,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 28.00,
                'currency' => 'INR',
                'description' => 'Paediatric oral suspension for fever and pain in children.',
            ],
            [
                'name' => 'Ibuprofen',
                'dosage' => '400 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 2500,
                'supplier' => 'Sun Pharmaceutical Industries Ltd',
                'unit_cost' => 1.40,
                'currency' => 'INR',
                'description' => 'NSAID tablets for pain, inflammation, and fever where not contraindicated.',
            ],

            // Antibiotics
            [
                'name' => 'Amoxicillin',
                'dosage' => '500 mg',
                'dosage_form' => 'tablet',
                'unit' => 'capsules',
                'reorder_point' => 3000,
                'supplier' => 'Sun Pharmaceutical Industries Ltd',
                'unit_cost' => 3.20,
                'currency' => 'INR',
                'description' => 'Broad-spectrum penicillin capsules for community-acquired infections.',
            ],
            [
                'name' => 'Amoxicillin',
                'dosage' => '250 mg/5 mL',
                'dosage_form' => 'syrup',
                'unit' => 'bottles (100 mL)',
                'reorder_point' => 1200,
                'supplier' => 'Sun Pharmaceutical Industries Ltd',
                'unit_cost' => 45.00,
                'currency' => 'INR',
                'description' => 'Paediatric powder for oral suspension (reconstituted at facility).',
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
                'description' => 'Third-generation cephalosporin powder for injection (IM/IV) for severe bacterial infection.',
            ],
            [
                'name' => 'Benzylpenicillin',
                'dosage' => '1.2 MIU',
                'dosage_form' => 'injection',
                'unit' => 'vials',
                'reorder_point' => 1000,
                'supplier' => 'Aurobindo Pharma Ltd',
                'unit_cost' => 18.00,
                'currency' => 'INR',
                'description' => 'Penicillin G sodium powder for injection for serious streptococcal and related infections.',
            ],
            [
                'name' => 'Benzathine benzylpenicillin',
                'dosage' => '2.4 MIU',
                'dosage_form' => 'injection',
                'unit' => 'vials',
                'reorder_point' => 600,
                'supplier' => 'Aurobindo Pharma Ltd',
                'unit_cost' => 55.00,
                'currency' => 'INR',
                'description' => 'Long-acting IM penicillin for syphilis treatment and rheumatic fever prophylaxis.',
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
                'description' => 'Antiprotozoal/anaerobic antibiotic tablets (e.g. amoebiasis, bacterial vaginosis, anaerobic infection).',
            ],
            [
                'name' => 'Co-trimoxazole',
                'dosage' => '400/80 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 2500,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 1.25,
                'currency' => 'INR',
                'description' => 'Sulfamethoxazole/trimethoprim tablets for UTI, PCP prophylaxis, and selected bacterial infections.',
            ],
            [
                'name' => 'Doxycycline',
                'dosage' => '100 mg',
                'dosage_form' => 'tablet',
                'unit' => 'capsules',
                'reorder_point' => 1800,
                'supplier' => 'Lupin Ltd',
                'unit_cost' => 2.10,
                'currency' => 'INR',
                'description' => 'Tetracycline-class antibiotic; also used in malaria and sexually transmitted infection pathways.',
            ],
            [
                'name' => 'Gentamicin',
                'dosage' => '40 mg/mL (2 mL)',
                'dosage_form' => 'injection',
                'unit' => 'ampoules',
                'reorder_point' => 900,
                'supplier' => 'Zhejiang Huahai Pharmaceutical Co., Ltd',
                'unit_cost' => 3.80,
                'currency' => 'CNY',
                'description' => 'Aminoglycoside injection for severe Gram-negative and neonatal sepsis regimens.',
            ],

            // Malaria (high burden in PNG)
            [
                'name' => 'Artemether/Lumefantrine',
                'dosage' => '20/120 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 4000,
                'supplier' => 'Aurobindo Pharma Ltd',
                'unit_cost' => 18.50,
                'currency' => 'INR',
                'description' => 'Fixed-dose ACT (Coartem-type) for uncomplicated Plasmodium falciparum malaria.',
            ],
            [
                'name' => 'Primaquine',
                'dosage' => '7.5 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 1500,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 4.50,
                'currency' => 'INR',
                'description' => '8-aminoquinoline for radical cure of P. vivax / gametocyte clearance (G6PD status applies).',
            ],
            [
                'name' => 'Quinine dihydrochloride',
                'dosage' => '300 mg/mL (2 mL)',
                'dosage_form' => 'injection',
                'unit' => 'ampoules',
                'reorder_point' => 400,
                'supplier' => "Dr. Reddy's Laboratories Ltd",
                'unit_cost' => 32.00,
                'currency' => 'INR',
                'description' => 'Parenteral quinine for severe malaria when artesunate is unavailable.',
            ],

            // Maternal & reproductive health
            [
                'name' => 'Oxytocin',
                'dosage' => '10 IU/mL (1 mL)',
                'dosage_form' => 'injection',
                'unit' => 'ampoules',
                'reorder_point' => 1200,
                'supplier' => 'Sinopharm International Corporation',
                'unit_cost' => 2.40,
                'currency' => 'CNY',
                'description' => 'Cold-chain injectable uterotonic for labour induction/augmentation and postpartum haemorrhage.',
            ],
            [
                'name' => 'Misoprostol',
                'dosage' => '200 mcg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 800,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 12.00,
                'currency' => 'INR',
                'description' => 'Prostaglandin analogue tablets used for postpartum haemorrhage where oxytocin is not available.',
            ],
            [
                'name' => 'Ferrous sulfate + folic acid',
                'dosage' => '200 mg + 400 mcg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 5000,
                'supplier' => 'Lupin Ltd',
                'unit_cost' => 0.95,
                'currency' => 'INR',
                'description' => 'Antenatal iron–folate supplementation for anaemia prevention in pregnancy.',
            ],

            // NCDs
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
                'name' => 'Amlodipine',
                'dosage' => '5 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 1800,
                'supplier' => 'Sun Pharmaceutical Industries Ltd',
                'unit_cost' => 1.05,
                'currency' => 'INR',
                'description' => 'Calcium-channel blocker for hypertension and angina.',
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
                'description' => 'Sulfonylurea for type 2 diabetes where metformin alone is insufficient.',
            ],

            // Fluids, ORS, emergency
            [
                'name' => 'Oral rehydration salts (WHO low-osmolarity)',
                'dosage' => '20.5 g sachet',
                'dosage_form' => 'other',
                'unit' => 'sachets',
                'reorder_point' => 10000,
                'supplier' => 'Sinopharm International Corporation',
                'unit_cost' => 1.60,
                'currency' => 'CNY',
                'description' => 'WHO low-osmolarity ORS sachets for dehydration from diarrhoea (dissolve in 1 L clean water).',
            ],
            [
                'name' => 'Zinc sulfate',
                'dosage' => '20 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 4000,
                'supplier' => 'CSPC Pharmaceutical Group Ltd',
                'unit_cost' => 0.70,
                'currency' => 'CNY',
                'description' => 'Dispersible zinc tablets given with ORS for childhood diarrhoea (10–14 day course).',
            ],
            [
                'name' => 'Sodium chloride IV infusion',
                'dosage' => '0.9% 500 mL',
                'dosage_form' => 'injection',
                'unit' => 'bags',
                'reorder_point' => 6000,
                'supplier' => 'Shanghai Pharmaceuticals Holding Co., Ltd',
                'unit_cost' => 4.25,
                'currency' => 'CNY',
                'description' => 'Normal saline 0.9% IV infusion bag for fluid resuscitation and drug dilution.',
            ],
            [
                'name' => 'Compound sodium lactate (Ringer\'s lactate)',
                'dosage' => '500 mL',
                'dosage_form' => 'injection',
                'unit' => 'bags',
                'reorder_point' => 3500,
                'supplier' => 'Shanghai Pharmaceuticals Holding Co., Ltd',
                'unit_cost' => 4.80,
                'currency' => 'CNY',
                'description' => 'Balanced crystalloid IV fluid for volume replacement and obstetric emergency care.',
            ],
            [
                'name' => 'Glucose IV infusion',
                'dosage' => '5% 500 mL',
                'dosage_form' => 'injection',
                'unit' => 'bags',
                'reorder_point' => 3000,
                'supplier' => 'Sinopharm International Corporation',
                'unit_cost' => 4.10,
                'currency' => 'CNY',
                'description' => 'Dextrose 5% IV infusion for hypoglycaemia support and maintenance fluids.',
            ],
            [
                'name' => 'Adrenaline (epinephrine)',
                'dosage' => '1 mg/mL (1 mL)',
                'dosage_form' => 'injection',
                'unit' => 'ampoules',
                'reorder_point' => 500,
                'supplier' => 'Zhejiang Huahai Pharmaceutical Co., Ltd',
                'unit_cost' => 2.20,
                'currency' => 'CNY',
                'description' => 'Emergency catecholamine for anaphylaxis, cardiac arrest, and severe asthma pathways.',
            ],

            // Neglected tropical / public health
            [
                'name' => 'Albendazole',
                'dosage' => '400 mg',
                'dosage_form' => 'tablet',
                'unit' => 'tablets',
                'reorder_point' => 5000,
                'supplier' => 'Cipla Ltd',
                'unit_cost' => 2.80,
                'currency' => 'INR',
                'description' => 'Broad-spectrum anthelmintic for soil-transmitted helminth mass-drug administration and individual treatment.',
            ],
        ];

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
