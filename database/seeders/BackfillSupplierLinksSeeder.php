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
            'Artemether' => 'Zhejiang Huahai Pharmaceutical Co., Ltd',
            'Artemisinin-based Combination' => 'Aurobindo Pharma Ltd',
            'Quinine' => "Dr. Reddy's Laboratories Ltd",
            'Isoniazid' => "Dr. Reddy's Laboratories Ltd",
            'Rifampicin' => 'Aurobindo Pharma Ltd',
            'Pyrazinamide' => 'Aurobindo Pharma Ltd',
            'Amoxicillin' => 'Sun Pharmaceutical Industries Ltd',
            'Ceftriaxone' => "Dr. Reddy's Laboratories Ltd",
            'Salbutamol' => 'Sun Pharmaceutical Industries Ltd',
            'ORS' => 'Sinopharm International Corporation',
            'Ciprofloxacin' => 'Lupin Ltd',
            'Metronidazole' => 'Lupin Ltd',
            'Paracetamol' => 'Cipla Ltd',
            'IV Fluids' => 'Shanghai Pharmaceuticals Holding Co., Ltd',
            'Tenofovir' => "Dr. Reddy's Laboratories Ltd",
            'Lamivudine' => 'Cipla Ltd',
            'Efavirenz' => 'Aurobindo Pharma Ltd',
            'Amlodipine' => 'Sun Pharmaceutical Industries Ltd',
            'Enalapril' => 'Lupin Ltd',
            'Metformin' => "Dr. Reddy's Laboratories Ltd",
            'Glibenclamide' => "Dr. Reddy's Laboratories Ltd",
            'Ferrous Sulfate' => 'Lupin Ltd',
            'Folic Acid' => 'Lupin Ltd',
            'Vitamin B12' => 'CSPC Pharmaceutical Group Ltd',
            'Chloramphenicol' => 'Lupin Ltd',
            'Levofloxacin' => "Dr. Reddy's Laboratories Ltd",
            'Albendazole' => 'Cipla Ltd',
            'Mebendazole' => 'Cipla Ltd',
            'Praziquantel' => 'Cipla Ltd',
            'Dapsone' => 'Lupin Ltd',
            'Epinephrine' => 'Zhejiang Huahai Pharmaceutical Co., Ltd',
            'Glucose' => 'Sinopharm International Corporation',
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
