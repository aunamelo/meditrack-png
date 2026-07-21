<?php

namespace Database\Seeders;

use App\Models\Drug;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DrugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a procurement officer user for created_by field
        $procurementOfficer = User::where('email', 'procurement@example.com')->first();
        if (!$procurementOfficer) {
            $procurementOfficer = User::first(); // Fallback to first user
        }

        // NDoH Level Drugs (3 drugs)
        Drug::create([
            'drug_name' => 'Paracetamol',
            'description' => 'Pain reliever and fever reducer',
            'dosage' => '500mg',
            'dosage_form' => 'tablet',
            'batch_number' => 'NDOH-PAR-2024-001',
            'expiry_date' => Carbon::now()->addYears(2),
            'quantity_received' => 50,
            'quantity_on_hand' => 50,
            'reorder_point' => 20,
            'unit' => 'tablets',
            'supplier' => 'PharmaCorp PNG',
            'cost_per_unit' => 0.50,
            'storage_location' => 'Shelf A1',
            'level' => 'ndoh',
            'status' => 'active',
            'received_date' => Carbon::now()->subDays(30),
            'notes' => 'Standard stock for distribution',
            'created_by' => $procurementOfficer->id,
            'updated_by' => $procurementOfficer->id,
        ]);

        Drug::create([
            'drug_name' => 'Ibuprofen',
            'description' => 'Anti-inflammatory pain medication',
            'dosage' => '200mg',
            'dosage_form' => 'tablet',
            'batch_number' => 'NDOH-IBU-2024-002',
            'expiry_date' => Carbon::now()->addYears(1),
            'quantity_received' => 10,
            'quantity_on_hand' => 10,
            'reorder_point' => 25,
            'unit' => 'tablets',
            'supplier' => 'MedSupply Ltd',
            'cost_per_unit' => 0.75,
            'storage_location' => 'Shelf A2',
            'level' => 'ndoh',
            'status' => 'active',
            'received_date' => Carbon::now()->subDays(15),
            'notes' => 'Low stock - needs reorder',
            'created_by' => $procurementOfficer->id,
            'updated_by' => $procurementOfficer->id,
        ]);

        Drug::create([
            'drug_name' => 'Amoxicillin',
            'description' => 'Antibiotic for bacterial infections',
            'dosage' => '500mg',
            'dosage_form' => 'tablet',
            'batch_number' => 'NDOH-AMX-2024-003',
            'expiry_date' => Carbon::now()->addMonths(4),
            'quantity_received' => 100,
            'quantity_on_hand' => 100,
            'reorder_point' => 50,
            'unit' => 'tablets',
            'supplier' => 'PharmaCorp PNG',
            'cost_per_unit' => 1.20,
            'storage_location' => 'Shelf B1',
            'level' => 'ndoh',
            'status' => 'active',
            'received_date' => Carbon::now()->subDays(60),
            'notes' => 'Expiring soon - prioritize distribution',
            'created_by' => $procurementOfficer->id,
            'updated_by' => $procurementOfficer->id,
        ]);

        // Lae AMS Level Drugs (3 drugs)
        Drug::create([
            'drug_name' => 'Metformin',
            'description' => 'Diabetes medication',
            'dosage' => '500mg',
            'dosage_form' => 'tablet',
            'batch_number' => 'LAE-MET-2024-004',
            'expiry_date' => Carbon::now()->addYears(3),
            'quantity_received' => 200,
            'quantity_on_hand' => 200,
            'reorder_point' => 80,
            'unit' => 'tablets',
            'supplier' => 'DiabetesCare PNG',
            'cost_per_unit' => 0.60,
            'storage_location' => 'Shelf C1',
            'level' => 'lae_ams',
            'status' => 'active',
            'received_date' => Carbon::now()->subDays(45),
            'notes' => 'Good stock level',
            'created_by' => $procurementOfficer->id,
            'updated_by' => $procurementOfficer->id,
        ]);

        Drug::create([
            'drug_name' => 'Ciprofloxacin',
            'description' => 'Broad-spectrum antibiotic',
            'dosage' => '500mg',
            'dosage_form' => 'tablet',
            'batch_number' => 'LAE-CIP-2024-005',
            'expiry_date' => Carbon::now()->addYears(2),
            'quantity_received' => 80,
            'quantity_on_hand' => 80,
            'reorder_point' => 40,
            'unit' => 'tablets',
            'supplier' => 'MedSupply Ltd',
            'cost_per_unit' => 1.50,
            'storage_location' => 'Shelf C2',
            'level' => 'lae_ams',
            'status' => 'active',
            'received_date' => Carbon::now()->subDays(20),
            'notes' => 'Standard antibiotic stock',
            'created_by' => $procurementOfficer->id,
            'updated_by' => $procurementOfficer->id,
        ]);

        Drug::create([
            'drug_name' => 'Vitamin C',
            'description' => 'Dietary supplement',
            'dosage' => '500mg',
            'dosage_form' => 'tablet',
            'batch_number' => 'LAE-VIT-2024-006',
            'expiry_date' => Carbon::now()->addYears(1),
            'quantity_received' => 500,
            'quantity_on_hand' => 500,
            'reorder_point' => 200,
            'unit' => 'tablets',
            'supplier' => 'HealthPlus PNG',
            'cost_per_unit' => 0.15,
            'storage_location' => 'Shelf D1',
            'level' => 'lae_ams',
            'status' => 'active',
            'received_date' => Carbon::now()->subDays(10),
            'notes' => 'High stock - supplements',
            'created_by' => $procurementOfficer->id,
            'updated_by' => $procurementOfficer->id,
        ]);

        // Modilon Hospital Level Drugs (4 drugs)
        Drug::create([
            'drug_name' => 'Paracetamol',
            'description' => 'Pain reliever and fever reducer',
            'dosage' => '500mg',
            'dosage_form' => 'tablet',
            'batch_number' => 'MOD-PAR-2024-007',
            'expiry_date' => Carbon::now()->addMonths(8),
            'quantity_received' => 30,
            'quantity_on_hand' => 5,
            'reorder_point' => 15,
            'unit' => 'tablets',
            'supplier' => 'PharmaCorp PNG',
            'cost_per_unit' => 0.50,
            'storage_location' => 'Pharmacy Shelf 1',
            'level' => 'modilon_hospital',
            'status' => 'active',
            'received_date' => Carbon::now()->subDays(90),
            'last_issued_date' => Carbon::now()->subDays(5),
            'notes' => 'Low stock - urgent reorder needed',
            'created_by' => $procurementOfficer->id,
            'updated_by' => $procurementOfficer->id,
        ]);

        Drug::create([
            'drug_name' => 'Aspirin',
            'description' => 'Pain reliever and blood thinner',
            'dosage' => '100mg',
            'dosage_form' => 'tablet',
            'batch_number' => 'MOD-ASP-2024-008',
            'expiry_date' => Carbon::now()->addYears(1),
            'quantity_received' => 50,
            'quantity_on_hand' => 30,
            'reorder_point' => 20,
            'unit' => 'tablets',
            'supplier' => 'MedSupply Ltd',
            'cost_per_unit' => 0.40,
            'storage_location' => 'Pharmacy Shelf 2',
            'level' => 'modilon_hospital',
            'status' => 'active',
            'received_date' => Carbon::now()->subDays(60),
            'last_issued_date' => Carbon::now()->subDays(2),
            'notes' => 'Cardiovascular ward stock',
            'created_by' => $procurementOfficer->id,
            'updated_by' => $procurementOfficer->id,
        ]);

        Drug::create([
            'drug_name' => 'Salbutamol',
            'description' => 'Bronchodilator for asthma',
            'dosage' => '100mcg',
            'dosage_form' => 'other',
            'batch_number' => 'MOD-SAL-2024-009',
            'expiry_date' => Carbon::now()->addMonths(18),
            'quantity_received' => 10,
            'quantity_on_hand' => 10,
            'reorder_point' => 5,
            'unit' => 'inhalers',
            'supplier' => 'RespiratoryCare PNG',
            'cost_per_unit' => 15.00,
            'storage_location' => 'Pharmacy Shelf 3',
            'level' => 'modilon_hospital',
            'status' => 'active',
            'received_date' => Carbon::now()->subDays(30),
            'notes' => 'Emergency respiratory medication',
            'created_by' => $procurementOfficer->id,
            'updated_by' => $procurementOfficer->id,
        ]);

        Drug::create([
            'drug_name' => 'Expired Test Drug',
            'description' => 'Test drug for expired status testing',
            'dosage' => '250mg',
            'dosage_form' => 'tablet',
            'batch_number' => 'MOD-EXP-2023-010',
            'expiry_date' => Carbon::now()->subMonths(2),
            'quantity_received' => 20,
            'quantity_on_hand' => 0,
            'reorder_point' => 10,
            'unit' => 'tablets',
            'supplier' => 'Test Supplier',
            'cost_per_unit' => 1.00,
            'storage_location' => 'Quarantine',
            'level' => 'modilon_hospital',
            'status' => 'expired',
            'received_date' => Carbon::now()->subMonths(12),
            'notes' => 'Expired - for testing purposes only',
            'created_by' => $procurementOfficer->id,
            'updated_by' => $procurementOfficer->id,
        ]);
    }
}
