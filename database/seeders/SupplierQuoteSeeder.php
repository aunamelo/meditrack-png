<?php

namespace Database\Seeders;

use App\Models\SupplierQuote;
use Illuminate\Database\Seeder;

class SupplierQuoteSeeder extends Seeder
{
    /**
     * Seed supplier price quotes for common NDoH medicines.
     */
    public function run(): void
    {
        $quotes = [
            // Amoxicillin 500mg
            ['drug_name' => 'Amoxicillin', 'dosage' => '500mg', 'supplier_name' => 'PharmaCorp India', 'country' => 'India', 'unit_price' => 82.00, 'quote_currency' => 'INR', 'min_order_qty' => 100, 'lead_time_days' => 28, 'source' => 'overseas', 'notes' => 'Bulk antibiotic supply — competitive Indian pricing'],
            ['drug_name' => 'Amoxicillin', 'dosage' => '500mg', 'supplier_name' => 'Global Health Distributors', 'country' => 'Australia', 'unit_price' => 1.35, 'quote_currency' => 'AUD', 'min_order_qty' => 200, 'lead_time_days' => 14, 'source' => 'overseas', 'notes' => 'Fast regional delivery via Lae port'],
            ['drug_name' => 'Amoxicillin', 'dosage' => '500mg', 'supplier_name' => 'Pacific Pharma Co.', 'country' => 'Singapore', 'unit_price' => 0.95, 'quote_currency' => 'USD', 'min_order_qty' => 500, 'lead_time_days' => 21, 'source' => 'overseas', 'notes' => 'WHO-prequalified manufacturer'],
            ['drug_name' => 'Amoxicillin', 'dosage' => '500mg', 'supplier_name' => 'MedSupply PNG Ltd', 'country' => 'Papua New Guinea', 'unit_price' => 4.80, 'quote_currency' => 'PGK', 'min_order_qty' => 50, 'lead_time_days' => 7, 'source' => 'local', 'notes' => 'Local distributor — shortest lead time'],
            ['drug_name' => 'Amoxicillin', 'dosage' => '500mg', 'supplier_name' => 'WHO Donation Program', 'country' => 'International', 'unit_price' => 0, 'quote_currency' => 'PGK', 'min_order_qty' => 1000, 'lead_time_days' => 45, 'source' => 'donation', 'notes' => 'Donated stock — subject to availability'],

            // Paracetamol 500mg
            ['drug_name' => 'Paracetamol', 'dosage' => '500mg', 'supplier_name' => 'SunPharma India', 'country' => 'India', 'unit_price' => 12.50, 'quote_currency' => 'INR', 'min_order_qty' => 500, 'lead_time_days' => 30, 'source' => 'overseas', 'notes' => 'High-volume pain relief tablets'],
            ['drug_name' => 'Paracetamol', 'dosage' => '500mg', 'supplier_name' => 'PharmaCorp International', 'country' => 'USA', 'unit_price' => 0.18, 'quote_currency' => 'USD', 'min_order_qty' => 1000, 'lead_time_days' => 35, 'source' => 'overseas', 'notes' => 'Premium grade — longer shelf life'],
            ['drug_name' => 'Paracetamol', 'dosage' => '500mg', 'supplier_name' => 'HealthPlus PNG', 'country' => 'Papua New Guinea', 'unit_price' => 0.55, 'quote_currency' => 'PGK', 'min_order_qty' => 100, 'lead_time_days' => 5, 'source' => 'local', 'notes' => 'Port Moresby warehouse stock'],
            ['drug_name' => 'Paracetamol', 'dosage' => '500mg', 'supplier_name' => 'NZ Med Imports', 'country' => 'New Zealand', 'unit_price' => 0.22, 'quote_currency' => 'NZD', 'min_order_qty' => 300, 'lead_time_days' => 18, 'source' => 'overseas', 'notes' => 'Regional supplier — reliable cold-chain'],

            // Ibuprofen 200mg
            ['drug_name' => 'Ibuprofen', 'dosage' => '200mg', 'supplier_name' => 'MedSupply Ltd', 'country' => 'India', 'unit_price' => 18.00, 'quote_currency' => 'INR', 'min_order_qty' => 200, 'lead_time_days' => 25, 'source' => 'overseas', 'notes' => 'Anti-inflammatory — standard ward stock'],
            ['drug_name' => 'Ibuprofen', 'dosage' => '200mg', 'supplier_name' => 'EuroPharm GmbH', 'country' => 'Germany', 'unit_price' => 0.28, 'quote_currency' => 'EUR', 'min_order_qty' => 500, 'lead_time_days' => 42, 'source' => 'overseas', 'notes' => 'EU-certified manufacturing'],
            ['drug_name' => 'Ibuprofen', 'dosage' => '200mg', 'supplier_name' => 'PharmaCorp PNG', 'country' => 'Papua New Guinea', 'unit_price' => 0.85, 'quote_currency' => 'PGK', 'min_order_qty' => 100, 'lead_time_days' => 10, 'source' => 'local', 'notes' => 'Local repackaging from imported bulk'],

            // Metformin 500mg
            ['drug_name' => 'Metformin', 'dosage' => '500mg', 'supplier_name' => 'DiabetesCare India', 'country' => 'India', 'unit_price' => 9.80, 'quote_currency' => 'INR', 'min_order_qty' => 500, 'lead_time_days' => 28, 'source' => 'overseas', 'notes' => 'Diabetes program essential medicine'],
            ['drug_name' => 'Metformin', 'dosage' => '500mg', 'supplier_name' => 'Asia Pacific Pharma', 'country' => 'Malaysia', 'unit_price' => 0.12, 'quote_currency' => 'USD', 'min_order_qty' => 1000, 'lead_time_days' => 21, 'source' => 'overseas', 'notes' => 'Competitive ASEAN pricing'],
            ['drug_name' => 'Metformin', 'dosage' => '500mg', 'supplier_name' => 'Lae Medical Supplies', 'country' => 'Papua New Guinea', 'unit_price' => 0.65, 'quote_currency' => 'PGK', 'min_order_qty' => 200, 'lead_time_days' => 7, 'source' => 'local', 'notes' => 'Madang province delivery available'],

            // Ciprofloxacin 500mg
            ['drug_name' => 'Ciprofloxacin', 'dosage' => '500mg', 'supplier_name' => 'PharmaCorp India', 'country' => 'India', 'unit_price' => 95.00, 'quote_currency' => 'INR', 'min_order_qty' => 100, 'lead_time_days' => 30, 'source' => 'overseas', 'notes' => 'Broad-spectrum antibiotic'],
            ['drug_name' => 'Ciprofloxacin', 'dosage' => '500mg', 'supplier_name' => 'Global Health Distributors', 'country' => 'Australia', 'unit_price' => 1.85, 'quote_currency' => 'AUD', 'min_order_qty' => 150, 'lead_time_days' => 16, 'source' => 'overseas', 'notes' => 'TGA-approved batches'],
            ['drug_name' => 'Ciprofloxacin', 'dosage' => '500mg', 'supplier_name' => 'MedSupply PNG Ltd', 'country' => 'Papua New Guinea', 'unit_price' => 6.20, 'quote_currency' => 'PGK', 'min_order_qty' => 50, 'lead_time_days' => 8, 'source' => 'local', 'notes' => 'Emergency stock replenishment'],
        ];

        foreach ($quotes as $quote) {
            SupplierQuote::create($quote);
        }
    }
}
