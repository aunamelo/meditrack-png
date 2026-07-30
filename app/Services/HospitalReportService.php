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
     *
     * @return array<string, mixed>
     */
    public static function generate(int $pharmacyManagerId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subDays(30)->startOfDay();
        $to = $to ?? now()->endOfDay();

        $inventory = Drug::query()
            ->atLevel('modilon_hospital')
            ->inInventory()
            ->get();

        $requests = HospitalOrder::query()
            ->where('requested_by', $pharmacyManagerId)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $incoming = StockTransfer::query()
            ->where('to_level', 'modilon_hospital')
            ->whereBetween('sent_date', [$from->toDateString(), $to->toDateString()])
            ->whereHas('hospitalOrder', fn ($q) => $q->where('requested_by', $pharmacyManagerId))
            ->get();

        $discrepancies = DiscrepancyReport::query()
            ->where('reported_by', $pharmacyManagerId)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $dispensing = DispensingRecord::query()
            ->whereBetween('dispensed_at', [$from, $to])
            ->get();

        $stockTakes = StockAdjustment::query()
            ->atLevel('modilon_hospital')
            ->whereBetween('adjusted_at', [$from, $to])
            ->get();

        return [
            'period' => [
                'from' => $from->format('M d, Y'),
                'to' => $to->format('M d, Y'),
            ],
            'inventory' => [
                'total_batches' => $inventory->count(),
                'total_units' => (int) $inventory->sum('quantity_on_hand'),
                'low_stock_batches' => $inventory->filter(fn (Drug $drug) => $drug->is_low_stock)->count(),
                'expiring_soon' => Drug::atLevel('modilon_hospital')->expiring()->count(),
                'expired' => Drug::atLevel('modilon_hospital')->expired()->count(),
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
            'recent_requests' => $requests->sortByDesc('created_at')->take(5)->values(),
            'recent_dispensing' => $dispensing->sortByDesc('dispensed_at')->take(5)->values(),
        ];
    }
}
