<?php

namespace App\Services;

use App\Models\DiscrepancyReport;
use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\StockTransfer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RegionalReportService
{
    /**
     * Build regional warehouse report data for the Store Manager.
     *
     * @return array<string, mixed>
     */
    public static function generate(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subDays(30)->startOfDay();
        $to = $to ?? now()->endOfDay();

        $hospitalOrders = HospitalOrder::query()
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $ndohReceipts = StockTransfer::query()
            ->where('to_level', 'lae_ams')
            ->whereBetween('sent_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $hospitalShipments = StockTransfer::query()
            ->where('from_level', 'lae_ams')
            ->where('to_level', 'modilon_hospital')
            ->whereBetween('sent_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $discrepancies = DiscrepancyReport::query()
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $inventory = Drug::query()
            ->atLevel('lae_ams')
            ->inInventory()
            ->get();

        return [
            'period' => [
                'from' => $from->format('M d, Y'),
                'to' => $to->format('M d, Y'),
            ],
            'inventory' => [
                'total_batches' => $inventory->count(),
                'total_units' => $inventory->sum('quantity_on_hand'),
                'low_stock_batches' => $inventory->filter(fn (Drug $drug) => $drug->quantity_on_hand <= ($drug->reorder_point ?? 0))->count(),
                'expiring_soon' => Drug::atLevel('lae_ams')->expiring()->count(),
            ],
            'hospital_orders' => [
                'total' => $hospitalOrders->count(),
                'pending' => $hospitalOrders->where('status', 'pending')->count(),
                'approved' => $hospitalOrders->where('status', 'approved')->count(),
                'rejected' => $hospitalOrders->where('status', 'rejected')->count(),
                'shipped' => $hospitalOrders->where('status', 'shipped')->count(),
                'received' => $hospitalOrders->where('status', 'received')->count(),
            ],
            'ndoh_receipts' => [
                'total' => $ndohReceipts->count(),
                'received' => $ndohReceipts->where('status', 'received')->count(),
                'awaiting' => $ndohReceipts->where('status', 'sent')->count(),
                'units_received' => $ndohReceipts->where('status', 'received')->sum('quantity_sent'),
            ],
            'hospital_shipments' => [
                'total' => $hospitalShipments->count(),
                'delivered' => $hospitalShipments->where('status', 'received')->count(),
                'in_transit' => $hospitalShipments->where('status', 'sent')->count(),
                'units_sent' => $hospitalShipments->sum('quantity_sent'),
            ],
            'discrepancies' => [
                'total' => $discrepancies->count(),
                'open' => $discrepancies->where('status', 'open')->count(),
                'resolved' => $discrepancies->where('status', 'resolved')->count(),
            ],
            'recent_hospital_orders' => $hospitalOrders->sortByDesc('created_at')->take(5)->values(),
            'recent_shipments' => $hospitalShipments->sortByDesc('sent_date')->take(5)->values(),
        ];
    }
}
