<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Lae AMS road fleet for hospital deliveries (Madang route).
     */
    public function run(): void
    {
        $vehicles = [
            ['name' => 'Lae AMS Cold Chain Truck 1', 'registration' => 'LAE-AMS-01', 'type' => 'truck'],
            ['name' => 'Lae AMS Delivery Van 2', 'registration' => 'LAE-AMS-02', 'type' => 'van'],
            ['name' => 'Lae AMS Utility Ute 3', 'registration' => 'LAE-AMS-03', 'type' => 'ute'],
            ['name' => 'Lae AMS Refrigerated Truck 4', 'registration' => 'LAE-AMS-04', 'type' => 'truck'],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::firstOrCreate(
                ['registration' => $vehicle['registration']],
                [
                    'name' => $vehicle['name'],
                    'type' => $vehicle['type'],
                    'depot' => 'lae_ams',
                    'is_active' => true,
                    'notes' => 'Assigned by Lae AMS Store Manager for Modilon Hospital road deliveries.',
                ]
            );
        }
    }
}
