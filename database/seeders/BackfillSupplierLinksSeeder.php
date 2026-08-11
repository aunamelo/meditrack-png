<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class BackfillSupplierLinksSeeder extends Seeder
{
    /**
     * Link existing catalog and order rows to registered India/China suppliers.
     */
    public function run(): void
    {
        $catalog = [
            'Paracetamol' => 'Cipla Ltd',
            'Ibuprofen' => 'Sun Pharmaceutical Industries Ltd',
            'Amoxicillin' => 'Sun Pharmaceutical Industries Ltd',
            'Ceftriaxone' => "Dr. Reddy's Laboratories Ltd",
            'Benzylpenicillin' => 'Aurobindo Pharma Ltd',
            'Benzathine benzylpenicillin' => 'Aurobindo Pharma Ltd',
            'Metronidazole' => 'Lupin Ltd',
            'Co-trimoxazole' => 'Cipla Ltd',
            'Doxycycline' => 'Lupin Ltd',
            'Gentamicin' => 'Zhejiang Huahai Pharmaceutical Co., Ltd',
            'Artemether/Lumefantrine' => 'Aurobindo Pharma Ltd',
            'Primaquine' => 'Cipla Ltd',
            'Quinine dihydrochloride' => "Dr. Reddy's Laboratories Ltd",
            'Oxytocin' => 'Sinopharm International Corporation',
            'Misoprostol' => 'Cipla Ltd',
            'Ferrous sulfate + folic acid' => 'Lupin Ltd',
            'Metformin' => "Dr. Reddy's Laboratories Ltd",
            'Amlodipine' => 'Sun Pharmaceutical Industries Ltd',
            'Glibenclamide' => "Dr. Reddy's Laboratories Ltd",
            'Oral rehydration salts (WHO low-osmolarity)' => 'Sinopharm International Corporation',
            'Zinc sulfate' => 'CSPC Pharmaceutical Group Ltd',
            'Sodium chloride IV infusion' => 'Shanghai Pharmaceuticals Holding Co., Ltd',
            'Compound sodium lactate (Ringer\'s lactate)' => 'Shanghai Pharmaceuticals Holding Co., Ltd',
            'Glucose IV infusion' => 'Sinopharm International Corporation',
            'Adrenaline (epinephrine)' => 'Zhejiang Huahai Pharmaceutical Co., Ltd',
            'Albendazole' => 'Cipla Ltd',
            // Legacy names (inactive after MedicineSeeder refresh)
            'ORS' => 'Sinopharm International Corporation',
            'Normal Saline' => 'Shanghai Pharmaceuticals Holding Co., Ltd',
        ];

        $suppliers = Supplier::query()->pluck('id', 'name');

        foreach (Medicine::query()->get() as $medicine) {
            $supplierName = $catalog[$medicine->name] ?? null;

            if ($supplierName && isset($suppliers[$supplierName])) {
                $medicine->update(['supplier_id' => $suppliers[$supplierName]]);
            }
        }

        foreach (Order::query()->whereNull('supplier_id')->get() as $order) {
            $supplier = Supplier::query()->where('name', $order->supplier)->first();

            if ($supplier) {
                $order->update(['supplier_id' => $supplier->id]);
            }
        }
    }
}
