<?php

namespace Database\Seeders;

use App\Models\Medicine;
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

        $medicines = Medicine::query()->active()->take(5)->get();

        if ($medicines->isEmpty() || ! $procurementOfficer) {
            $this->command?->warn('OrderSeeder skipped: requires medicines and users.');

            return;
        }

        $suppliers = [
            'PharmaCorp International',
            'MedSupply PNG Ltd',
            'Global Health Distributors',
            'Pacific Pharma Co.',
            'WHO Donation Program',
        ];

        foreach (range(1, 3) as $i) {
            $this->createOrderWithItems([
                'order_number' => 'ORD-2026-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'supplier' => $suppliers[$i - 1],
                'order_date' => Carbon::now()->subDays(10 - $i),
                'expected_delivery_date' => Carbon::now()->addDays(30 + ($i * 7)),
                'source' => $i === 3 ? 'donation' : 'overseas',
                'status' => 'pending',
                'notes' => "Pending order #{$i} awaiting NDoH approval.",
                'created_by' => $procurementOfficer->id,
            ], [
                ['medicine_id' => $medicines[$i % $medicines->count()]->id, 'quantity_ordered' => 500 * $i],
            ]);
        }

        if ($medicines->count() >= 2) {
            $this->createOrderWithItems([
                'order_number' => 'ORD-2026-013',
                'supplier' => 'Combined Pharma Suppliers Ltd',
                'order_date' => Carbon::now()->subDays(2),
                'expected_delivery_date' => Carbon::now()->addDays(45),
                'source' => 'overseas',
                'status' => 'pending',
                'notes' => 'Multi-medicine procurement order awaiting NDoH approval.',
                'created_by' => $procurementOfficer->id,
            ], [
                ['medicine_id' => $medicines[0]->id, 'quantity_ordered' => 800],
                ['medicine_id' => $medicines[1]->id, 'quantity_ordered' => 1200],
                ['medicine_id' => $medicines[2 % $medicines->count()]->id, 'quantity_ordered' => 600],
            ]);
        }

        foreach (range(4, 6) as $i) {
            $this->createOrderWithItems([
                'order_number' => 'ORD-2026-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
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
            ], [
                ['medicine_id' => $medicines[($i - 1) % $medicines->count()]->id, 'quantity_ordered' => 1000],
            ]);
        }

        foreach (range(7, 9) as $i) {
            $this->createOrderWithItems([
                'order_number' => 'ORD-2026-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
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
            ], [
                ['medicine_id' => $medicines[($i - 1) % $medicines->count()]->id, 'quantity_ordered' => 750, 'quantity_received' => 750],
            ]);
        }

        foreach (range(10, 11) as $i) {
            $this->createOrderWithItems([
                'order_number' => 'ORD-2026-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
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
            ], [
                ['medicine_id' => $medicines[($i - 1) % $medicines->count()]->id, 'quantity_ordered' => 2000, 'quantity_received' => 1200],
            ]);
        }

        $this->createOrderWithItems([
            'order_number' => 'ORD-2026-012',
            'supplier' => 'Discontinued Supplier Ltd',
            'order_date' => Carbon::now()->subDays(15),
            'expected_delivery_date' => Carbon::now()->addDays(20),
            'source' => 'overseas',
            'status' => 'cancelled',
            'notes' => "Supplier unable to fulfil order.\n\nCancelled: Supplier contract terminated.",
            'created_by' => $procurementOfficer->id,
        ], [
            ['medicine_id' => $medicines->first()->id, 'quantity_ordered' => 300],
        ]);
    }

    /**
     * @param  array<string, mixed>  $orderData
     * @param  array<int, array<string, mixed>>  $lineItems
     */
    private function createOrderWithItems(array $orderData, array $lineItems): Order
    {
        $orderData['medicine_id'] = $lineItems[0]['medicine_id'];
        $orderData['quantity_ordered'] = collect($lineItems)->sum('quantity_ordered');
        $orderData['quantity_received'] = collect($lineItems)->sum(fn ($line) => $line['quantity_received'] ?? 0);

        $order = Order::create($orderData);

        foreach ($lineItems as $line) {
            $order->items()->create([
                'medicine_id' => $line['medicine_id'],
                'quantity_ordered' => $line['quantity_ordered'],
                'quantity_received' => $line['quantity_received'] ?? 0,
            ]);
        }

        return $order;
    }
}
