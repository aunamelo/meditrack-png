<?php

namespace App\Services;

use App\Models\DiscrepancyReport;
use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\StockTransfer;
use Carbon\Carbon;

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
            ->with(['requester'])
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $ndohReceipts = StockTransfer::query()
            ->with(['drug', 'sender'])
            ->where('to_level', 'lae_ams')
            ->whereNull('hospital_order_id')
            ->whereIn('status', ['sent', 'received'])
            ->whereBetween('sent_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $hospitalShipments = StockTransfer::query()
            ->with(['drug', 'hospitalOrder'])
            ->where('from_level', 'lae_ams')
            ->where('to_level', 'modilon_hospital')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('sent_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $discrepancies = DiscrepancyReport::query()
            ->whereBetween('created_at', [$from, $to])
            ->where(function ($query) {
                $query->whereHas(
                    'stockTransfer',
                    fn ($q) => $q->where(function ($corridor) {
                        $corridor->where(function ($leg) {
                            $leg->where('from_level', 'ndoh')->where('to_level', 'lae_ams');
                        })->orWhere(function ($leg) {
                            $leg->where('from_level', 'lae_ams')->where('to_level', 'modilon_hospital');
                        });
                    })
                )->orWhereHas('hospitalOrder');
            })
            ->get();

        $inventory = Drug::query()
            ->atLevel('lae_ams')
            ->inInventory()
            ->get();

        return [
            'title' => 'Lae AMS Regional Report',
            'facility' => 'Lae Area Medical Store',
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
                'expiring_soon' => Drug::atLevel('lae_ams')->expiring()->count(),
                'as_of' => 'now',
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
                'units_received' => (int) $ndohReceipts->where('status', 'received')->sum('quantity_sent'),
            ],
            'hospital_shipments' => [
                'total' => $hospitalShipments->count(),
                'delivered' => $hospitalShipments->where('status', 'received')->count(),
                'in_transit' => $hospitalShipments->where('status', 'sent')->count(),
                'units_sent' => (int) $hospitalShipments->sum('quantity_sent'),
            ],
            'discrepancies' => [
                'total' => $discrepancies->count(),
                'open' => $discrepancies->where('status', 'open')->count(),
                'resolved' => $discrepancies->where('status', 'resolved')->count(),
            ],
            'recent_hospital_orders' => $hospitalOrders->sortByDesc('created_at')->take(8)->values(),
            'recent_shipments' => $hospitalShipments->sortByDesc('sent_date')->take(8)->values(),
        ];
    }

    /**
     * Flatten report metrics into CSV rows.
     *
     * @param  array<string, mixed>  $report
     * @return array<int, array{0: string, 1: string|int}>
     */
    public static function toCsvRows(array $report): array
    {
        $rows = [
            ['Report', $report['title']],
            ['Facility', $report['facility']],
            ['Period from', $report['period']['from']],
            ['Period to', $report['period']['to']],
            ['Generated at', $report['generated_at']],
            ['Generated by', $report['generated_by']],
            ['', ''],
            ['Metric', 'Value'],
            ['Lae AMS batches (on hand now)', $report['inventory']['total_batches']],
            ['Units on hand (now)', $report['inventory']['total_units']],
            ['Low stock batches', $report['inventory']['low_stock_batches']],
            ['Expiring soon', $report['inventory']['expiring_soon']],
            ['Hospital orders total', $report['hospital_orders']['total']],
            ['Hospital orders pending', $report['hospital_orders']['pending']],
            ['Hospital orders approved', $report['hospital_orders']['approved']],
            ['Hospital orders rejected', $report['hospital_orders']['rejected']],
            ['Hospital orders shipped', $report['hospital_orders']['shipped']],
            ['Hospital orders received', $report['hospital_orders']['received']],
            ['NDoH shipments received', $report['ndoh_receipts']['received']],
            ['NDoH shipments awaiting receipt', $report['ndoh_receipts']['awaiting']],
            ['NDoH units received', $report['ndoh_receipts']['units_received']],
            ['Hospital road deliveries', $report['hospital_shipments']['total']],
            ['Hospital deliveries in transit', $report['hospital_shipments']['in_transit']],
            ['Hospital deliveries completed', $report['hospital_shipments']['delivered']],
            ['Hospital units dispatched', $report['hospital_shipments']['units_sent']],
            ['Discrepancies open', $report['discrepancies']['open']],
            ['Discrepancies resolved', $report['discrepancies']['resolved']],
        ];

        return $rows;
    }
}
