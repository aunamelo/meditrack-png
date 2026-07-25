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
            'Amoxicillin' => 'Sun Pharmaceutical Industries Ltd',
            'Metformin' => "Dr. Reddy's Laboratories Ltd",
            'Artemether/Lumefantrine' => 'Aurobindo Pharma Ltd',
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
