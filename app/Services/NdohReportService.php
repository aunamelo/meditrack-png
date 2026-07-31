<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\Order;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use Carbon\Carbon;

class NdohReportService
{
    /**
     * National NDoH period summary for Admin.
     *
     * @return array<string, mixed>
     */
    public static function generate(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subDays(30)->startOfDay();
        $to = $to ?? now()->endOfDay();

        $inventory = Drug::query()
            ->atLevel('ndoh')
            ->inInventory()
            ->get();

        $orders = Order::query()
            ->with(['creator', 'items.drug', 'drug'])
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $shipments = StockTransfer::query()
            ->with(['drug', 'sender', 'approver'])
            ->fromLevel('ndoh')
            ->toLevel('lae_ams')
            ->whereNull('hospital_order_id')
            ->whereBetween('sent_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $stockTakes = StockAdjustment::query()
            ->atLevel('ndoh')
            ->whereBetween('adjusted_at', [$from, $to])
            ->get();

        $laeUnits = (int) Drug::query()->atLevel('lae_ams')->inInventory()->sum('quantity_on_hand');
        $modilonUnits = (int) Drug::query()->atLevel('modilon_hospital')->inInventory()->sum('quantity_on_hand');

        $orderStatusCounts = [
            'pending' => $orders->where('status', 'pending')->count(),
            'manufacturing' => $orders->where('status', 'manufacturing')->count(),
            'shipped' => $orders->where('status', 'shipped')->count(),
            'customs' => $orders->where('status', 'customs')->count(),
            'fx_cleared' => $orders->where('status', 'fx_cleared')->count(),
            'partial' => $orders->where('status', 'partial')->count(),
            'received' => $orders->where('status', 'received')->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
        ];

        $activeOrders = $orders->where('status', '!=', 'cancelled');
        $spentOrders = $orders->whereIn('status', ['received', 'partial']);
        $pipelineOrders = $orders->whereIn('status', ['manufacturing', 'shipped', 'customs', 'fx_cleared']);
        $pendingOrders = $orders->where('status', 'pending');

        $spendBySupplier = $activeOrders
            ->groupBy(fn (Order $order) => $order->supplier ?: 'Unspecified supplier')
            ->map(fn ($group, $supplier) => [
                'supplier' => $supplier,
                'orders' => $group->count(),
                'amount' => round((float) $group->sum(fn (Order $order) => (float) ($order->invoice_amount ?? 0)), 2),
            ])
            ->sortByDesc('amount')
            ->take(8)
            ->values();

        $inventoryValue = round((float) $inventory->sum(
            fn (Drug $drug) => ((float) ($drug->cost_per_unit ?? 0)) * (int) $drug->quantity_on_hand
        ), 2);

        return [
            'title' => 'NDoH National Report',
            'facility' => 'National Department of Health — Central Store',
            'period' => [
                'from' => $from->format('M d, Y'),
                'to' => $to->format('M d, Y'),
                'from_raw' => $from->toDateString(),
                'to_raw' => $to->toDateString(),
            ],
            'generated_at' => now()->format('M d, Y H:i'),
            'generated_by' => auth()->user()?->name ?? 'System',
            'inventory' => [
                'total_batches' => $inventory->count(),
                'total_units' => (int) $inventory->sum('quantity_on_hand'),
                'low_stock_batches' => $inventory->filter(fn (Drug $drug) => $drug->quantity_on_hand <= ($drug->reorder_point ?? 0))->count(),
                'expiring_soon' => Drug::atLevel('ndoh')->expiring()->count(),
                'expired' => Drug::atLevel('ndoh')->expired()->count(),
            ],
            'corridor' => [
                'ndoh_units' => (int) $inventory->sum('quantity_on_hand'),
                'lae_ams_units' => $laeUnits,
                'modilon_units' => $modilonUnits,
            ],
            'orders' => array_merge([
                'total' => $orders->count(),
                'units_ordered' => (int) $orders->sum(fn (Order $order) => $order->quantity_ordered ?? $order->items->sum('quantity_ordered')),
                'units_received' => (int) $orders->sum(fn (Order $order) => $order->quantity_received ?? $order->items->sum('quantity_received')),
            ], $orderStatusCounts),
            'spending' => [
                'currency' => 'PGK',
                'amount_spent' => round((float) $spentOrders->sum(fn (Order $order) => (float) ($order->invoice_amount ?? 0)), 2),
                'amount_in_pipeline' => round((float) $pipelineOrders->sum(fn (Order $order) => (float) ($order->invoice_amount ?? 0)), 2),
                'amount_pending_approval' => round((float) $pendingOrders->sum(fn (Order $order) => (float) ($order->invoice_amount ?? 0)), 2),
                'amount_committed' => round((float) $activeOrders->sum(fn (Order $order) => (float) ($order->invoice_amount ?? 0)), 2),
                'orders_with_invoice' => $activeOrders->filter(fn (Order $order) => (float) ($order->invoice_amount ?? 0) > 0)->count(),
                'inventory_value' => $inventoryValue,
                'by_supplier' => $spendBySupplier,
            ],
            'shipments' => [
                'total' => $shipments->count(),
                'pending' => $shipments->where('status', 'pending')->count(),
                'sent' => $shipments->where('status', 'sent')->count(),
                'received' => $shipments->where('status', 'received')->count(),
                'cancelled' => $shipments->where('status', 'cancelled')->count(),
                'units_pending' => (int) $shipments->where('status', 'pending')->sum('quantity_sent'),
                'units_in_transit' => (int) $shipments->where('status', 'sent')->sum('quantity_sent'),
                'units_received_at_lae' => (int) $shipments->where('status', 'received')->sum('quantity_sent'),
            ],
            'stock_takes' => [
                'total' => $stockTakes->count(),
                'net_delta' => (int) $stockTakes->sum('quantity_delta'),
            ],
            'recent_orders' => $orders->sortByDesc('created_at')->take(8)->values(),
            'recent_shipments' => $shipments->sortByDesc('created_at')->take(8)->values(),
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<int, array{0: string, 1: string|int}>
     */
    public static function toCsvRows(array $report): array
    {
        return [
            ['Report', $report['title']],
            ['Facility', $report['facility']],
            ['Period from', $report['period']['from']],
            ['Period to', $report['period']['to']],
            ['Generated at', $report['generated_at']],
            ['Generated by', $report['generated_by']],
            ['', ''],
            ['Metric', 'Value'],
            ['NDoH batches', $report['inventory']['total_batches']],
            ['NDoH units on hand', $report['inventory']['total_units']],
            ['NDoH low stock batches', $report['inventory']['low_stock_batches']],
            ['NDoH expiring soon', $report['inventory']['expiring_soon']],
            ['NDoH expired', $report['inventory']['expired']],
            ['Corridor NDoH units', $report['corridor']['ndoh_units']],
            ['Corridor Lae AMS units', $report['corridor']['lae_ams_units']],
            ['Corridor Modilon units', $report['corridor']['modilon_units']],
            ['Procurement orders total', $report['orders']['total']],
            ['Orders pending approval', $report['orders']['pending']],
            ['Orders manufacturing', $report['orders']['manufacturing']],
            ['Orders shipped', $report['orders']['shipped']],
            ['Orders customs', $report['orders']['customs']],
            ['Orders FX cleared', $report['orders']['fx_cleared']],
            ['Orders partial', $report['orders']['partial']],
            ['Orders received', $report['orders']['received']],
            ['Orders cancelled', $report['orders']['cancelled']],
            ['Units ordered', $report['orders']['units_ordered']],
            ['Units received from suppliers', $report['orders']['units_received']],
            ['Amount spent (received/partial) PGK', $report['spending']['amount_spent']],
            ['Amount in pipeline PGK', $report['spending']['amount_in_pipeline']],
            ['Amount pending approval PGK', $report['spending']['amount_pending_approval']],
            ['Amount committed (excl. cancelled) PGK', $report['spending']['amount_committed']],
            ['Orders with invoice amount', $report['spending']['orders_with_invoice']],
            ['NDoH inventory value PGK', $report['spending']['inventory_value']],
            ['Shipments to Lae AMS total', $report['shipments']['total']],
            ['Shipments pending approval', $report['shipments']['pending']],
            ['Shipments in transit', $report['shipments']['sent']],
            ['Shipments received at Lae', $report['shipments']['received']],
            ['Units pending approval', $report['shipments']['units_pending']],
            ['Units in transit to Lae', $report['shipments']['units_in_transit']],
            ['Units received at Lae', $report['shipments']['units_received_at_lae']],
            ['Stock takes', $report['stock_takes']['total']],
            ['Stock take net delta', $report['stock_takes']['net_delta']],
            ['', ''],
            ['Spend by supplier', 'Amount PGK'],
            ...collect($report['spending']['by_supplier'])->map(
                fn (array $row) => [$row['supplier'].' ('.$row['orders'].' orders)', $row['amount']]
            )->all(),
        ];
    }
}
