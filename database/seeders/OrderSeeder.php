<?php

namespace Database\Seeders;

use App\Models\Drug;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Seed sample procurement orders across all workflow statuses.
     */
    public function run(): void
    {
        $procurementOfficer = User::where('email', 'procurement@example.com')->first() ?? User::first();
        $admin = User::where('email', 'admin@example.com')->first() ?? User::skip(1)->first() ?? $procurementOfficer;

        $drugs = Drug::query()->where('level', 'ndoh')->take(5)->get();

        if ($drugs->isEmpty()) {
            $drugs = Drug::query()->take(5)->get();
        }

        if ($drugs->isEmpty() || ! $procurementOfficer) {
            $this->command?->warn('OrderSeeder skipped: requires drugs and users.');

            return;
        }

        $suppliers = [
            'PharmaCorp International',
            'MedSupply PNG Ltd',
            'Global Health Distributors',
            'Pacific Pharma Co.',
            'WHO Donation Program',
        ];

        // 3 Pending orders
        foreach (range(1, 3) as $i) {
            Order::create([
                'order_number' => 'ORD-2026-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'drug_id' => $drugs[$i % $drugs->count()]->id,
                'quantity_ordered' => 500 * $i,
                'supplier' => $suppliers[$i - 1],
                'order_date' => Carbon::now()->subDays(10 - $i),
                'expected_delivery_date' => Carbon::now()->addDays(30 + ($i * 7)),
                'source' => $i === 3 ? 'donation' : 'overseas',
                'status' => 'pending',
                'notes' => "Pending order #{$i} awaiting NDoH approval.",
                'created_by' => $procurementOfficer->id,
            ]);
        }

        // 3 Approved/Ordered (awaiting delivery)
        foreach (range(4, 6) as $i) {
            Order::create([
                'order_number' => 'ORD-2026-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'drug_id' => $drugs[($i - 1) % $drugs->count()]->id,
                'quantity_ordered' => 1000,
                'supplier' => $suppliers[($i - 1) % count($suppliers)],
                'order_date' => Carbon::now()->subDays(20),
                'expected_delivery_date' => Carbon::now()->addDays(14),
                'supplier_invoice' => 'INV-2026-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'invoice_amount' => 15000 + ($i * 2500),
                'source' => 'overseas',
                'status' => 'ordered',
                'notes' => 'Approved and awaiting supplier shipment.',
                'created_by' => $procurementOfficer->id,
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(5),
            ]);
        }

        // 3 Received (complete deliveries)
        foreach (range(7, 9) as $i) {
            Order::create([
                'order_number' => 'ORD-2026-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'drug_id' => $drugs[($i - 1) % $drugs->count()]->id,
                'quantity_ordered' => 750,
                'quantity_received' => 750,
                'supplier' => $suppliers[($i - 1) % count($suppliers)],
                'order_date' => Carbon::now()->subDays(45),
                'expected_delivery_date' => Carbon::now()->subDays(10),
                'actual_delivery_date' => Carbon::now()->subDays(8),
                'supplier_invoice' => 'INV-2026-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'invoice_amount' => 22000,
                'source' => 'local',
                'status' => 'received',
                'notes' => 'Full delivery received at NDoH warehouse.',
                'created_by' => $procurementOfficer->id,
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(40),
                'received_by' => $admin->id,
                'received_at' => Carbon::now()->subDays(8),
            ]);
        }

        // 2 Partial deliveries
        foreach (range(10, 11) as $i) {
            Order::create([
                'order_number' => 'ORD-2026-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'drug_id' => $drugs[($i - 1) % $drugs->count()]->id,
                'quantity_ordered' => 2000,
                'quantity_received' => 1200,
                'supplier' => $suppliers[($i - 1) % count($suppliers)],
                'order_date' => Carbon::now()->subDays(30),
                'expected_delivery_date' => Carbon::now()->subDays(5),
                'actual_delivery_date' => Carbon::now()->subDays(3),
                'source' => 'overseas',
                'status' => 'partial',
                'notes' => 'Partial shipment received — balance expected.',
                'created_by' => $procurementOfficer->id,
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(25),
                'received_by' => $admin->id,
                'received_at' => Carbon::now()->subDays(3),
            ]);
        }

        // 1 Cancelled order
        Order::create([
            'order_number' => 'ORD-2026-012',
            'drug_id' => $drugs->first()->id,
            'quantity_ordered' => 300,
            'supplier' => 'Discontinued Supplier Ltd',
            'order_date' => Carbon::now()->subDays(15),
            'expected_delivery_date' => Carbon::now()->addDays(20),
            'source' => 'overseas',
            'status' => 'cancelled',
            'notes' => "Supplier unable to fulfil order.\n\nCancelled: Supplier contract terminated.",
            'created_by' => $procurementOfficer->id,
        ]);
    }
}
