<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Foundation data only — no inventory, orders, patients, or transfers.
     * Walk the full corridor from empty stock using the app.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(SupplierSeeder::class); // catalogue FK for medicines
        $this->call(VehicleSeeder::class);
        $this->call(MedicineSeeder::class); // NDoH procurement catalogue (not stock)

        // Not seeded (kept on disk for optional demos):
        // DrugSeeder, OrderSeeder, PatientSeeder, BackfillSupplierLinksSeeder
    }
}
