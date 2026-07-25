<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Registered pharmaceutical suppliers — aligned with NDoH import reliance on Asia (India & China).
     */
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Cipla Ltd', 'country' => 'india', 'headquarters' => 'Mumbai, India'],
            ['name' => 'Sun Pharmaceutical Industries Ltd', 'country' => 'india', 'headquarters' => 'Mumbai, India'],
            ['name' => "Dr. Reddy's Laboratories Ltd", 'country' => 'india', 'headquarters' => 'Hyderabad, India'],
            ['name' => 'Aurobindo Pharma Ltd', 'country' => 'india', 'headquarters' => 'Hyderabad, India'],
            ['name' => 'Lupin Ltd', 'country' => 'india', 'headquarters' => 'Pune, India'],
            ['name' => 'Sinopharm International Corporation', 'country' => 'china', 'headquarters' => 'Shanghai, China'],
            ['name' => 'Shanghai Pharmaceuticals Holding Co., Ltd', 'country' => 'china', 'headquarters' => 'Shanghai, China'],
            ['name' => 'Zhejiang Huahai Pharmaceutical Co., Ltd', 'country' => 'china', 'headquarters' => 'Linhai, China'],
            ['name' => 'CSPC Pharmaceutical Group Ltd', 'country' => 'china', 'headquarters' => 'Shijiazhuang, China'],
            ['name' => 'Boroko Pharmacy Wholesale', 'country' => 'png', 'headquarters' => 'Port Moresby, PNG'],
            ['name' => 'WHO Essential Medicines Programme', 'country' => 'international', 'headquarters' => 'Geneva, Switzerland'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['name' => $supplier['name']],
                [
                    'country' => $supplier['country'],
                    'headquarters' => $supplier['headquarters'],
                    'is_active' => true,
                ]
            );
        }
    }
}
