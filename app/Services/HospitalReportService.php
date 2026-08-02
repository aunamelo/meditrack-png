<?php

namespace App\Services;

use App\Models\DiscrepancyReport;
use App\Models\DispensingRecord;
use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use Carbon\Carbon;

class HospitalReportService
{
    /**
     * Modilon hospital period summary for Pharmacy Manager.
     * Metrics are facility-scoped (Modilon), not limited to the logged-in user.
     *
     * @return array<string, mixed>
     */
    public static function generate(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subDays(30)->startOfDay();
        $to = $to ?? now()->endOfDay();

        $inventory = Drug::query()
            ->atLevel('modilon_hospital')
            ->inInventory()
            ->get();

        // Single-hospital corridor: all hospital orders are Modilon requests.
        $requests = HospitalOrder::query()
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $incoming = StockTransfer::query()
            ->with(['drug', 'hospitalOrder'])
            ->where('to_level', 'modilon_hospital')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('sent_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $discrepancies = DiscrepancyReport::query()
            ->whereBetween('created_at', [$from, $to])
            ->where(function ($query) {
                $query->whereHas('stockTransfer', fn ($q) => $q->where('to_level', 'modilon_hospital'))
                    ->orWhereHas('hospitalOrder');
            })
            ->get();

        $dispensing = DispensingRecord::query()
            ->with(['drug', 'patient'])
            ->whereBetween('dispensed_at', [$from, $to])
            ->whereHas('drug', fn ($q) => $q->atLevel('modilon_hospital'))
            ->latest('dispensed_at')
            ->get();

        $stockTakes = StockAdjustment::query()
            ->atLevel('modilon_hospital')
            ->whereBetween('adjusted_at', [$from, $to])
            ->get();

        return [
            'title' => 'Modilon Hospital Report',
            'facility' => 'Modilon General Hospital Pharmacy',
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
                'low_stock_batches' => $inventory->filter(fn (Drug $drug) => $drug->is_low_stock)->count(),
                'expiring_soon' => Drug::atLevel('modilon_hospital')->expiring()->count(),
                'expired' => Drug::atLevel('modilon_hospital')->expired()->count(),
                'as_of' => 'now',
            ],
            'requests' => [
                'total' => $requests->count(),
                'pending' => $requests->where('status', 'pending')->count(),
                'approved' => $requests->where('status', 'approved')->count(),
                'rejected' => $requests->where('status', 'rejected')->count(),
                'shipped' => $requests->where('status', 'shipped')->count(),
                'received' => $requests->where('status', 'received')->count(),
                'units_requested' => (int) $requests->sum('quantity_requested'),
            ],
            'deliveries' => [
                'total' => $incoming->count(),
                'in_transit' => $incoming->where('status', 'sent')->count(),
                'received' => $incoming->where('status', 'received')->count(),
                'units_dispatched' => (int) $incoming->sum('quantity_sent'),
            ],
            'dispensing' => [
                'records' => $dispensing->count(),
                'units_dispensed' => (int) $dispensing->sum('quantity_dispensed'),
            ],
            'discrepancies' => [
                'total' => $discrepancies->count(),
                'open' => $discrepancies->where('status', 'open')->count(),
                'resolved' => $discrepancies->where('status', 'resolved')->count(),
            ],
            'stock_takes' => [
                'total' => $stockTakes->count(),
                'net_delta' => (int) $stockTakes->sum('quantity_delta'),
            ],
            'recent_requests' => $requests->sortByDesc('created_at')->take(8)->values(),
            'recent_dispensing' => $dispensing->take(8)->values(),
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
        return [
            ['Report', $report['title']],
            ['Facility', $report['facility']],
            ['Period from', $report['period']['from']],
            ['Period to', $report['period']['to']],
            ['Generated at', $report['generated_at']],
            ['Generated by', $report['generated_by']],
            ['', ''],
            ['Metric', 'Value'],
            ['Stock batches (on hand now)', $report['inventory']['total_batches']],
            ['Units on hand (now)', $report['inventory']['total_units']],
            ['Low stock batches', $report['inventory']['low_stock_batches']],
            ['Expiring soon', $report['inventory']['expiring_soon']],
            ['Expired batches', $report['inventory']['expired']],
            ['Requests filed', $report['requests']['total']],
            ['Requests pending', $report['requests']['pending']],
            ['Requests approved', $report['requests']['approved']],
            ['Requests rejected', $report['requests']['rejected']],
            ['Requests shipped', $report['requests']['shipped']],
            ['Requests received', $report['requests']['received']],
            ['Units requested', $report['requests']['units_requested']],
            ['Deliveries total', $report['deliveries']['total']],
            ['Deliveries in transit', $report['deliveries']['in_transit']],
            ['Deliveries received', $report['deliveries']['received']],
            ['Units dispatched', $report['deliveries']['units_dispatched']],
            ['Dispensing records', $report['dispensing']['records']],
            ['Units dispensed', $report['dispensing']['units_dispensed']],
            ['Discrepancies open', $report['discrepancies']['open']],
            ['Discrepancies resolved', $report['discrepancies']['resolved']],
            ['Stock takes', $report['stock_takes']['total']],
            ['Stock take net delta', $report['stock_takes']['net_delta']],
        ];
    }
}
