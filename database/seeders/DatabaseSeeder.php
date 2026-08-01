<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(SupplierSeeder::class); // needed for medicine catalogue supplier links
        $this->call(VehicleSeeder::class);
        $this->call(MedicineSeeder::class);
        // Demo/domain seeders kept on disk but not run automatically:
        // $this->call(BackfillSupplierLinksSeeder::class);
        // $this->call(OrderSeeder::class);
        // $this->call(PatientSeeder::class);
    }
}
