<?php

namespace App\Http\Controllers;

use App\Services\LmisService;
use App\Services\PortalNavigationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockStatusController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        if (! $user->hasAnyRole(['admin', 'procurement_officer', 'store_manager', 'pharmacy_manager', 'pharmacist'])) {
            abort(403, 'You do not have access to stock status reports.');
        }

        $meta = PortalNavigationService::currentRoleMeta();
        $defaultLevel = $meta['inventory_level'] ?? 'modilon_hospital';

        $allowedLevels = match (true) {
            $user->hasAnyRole(['admin', 'procurement_officer']) => ['ndoh', 'lae_ams', 'modilon_hospital', 'corridor'],
            $user->hasRole('store_manager') => ['lae_ams', 'modilon_hospital'],
            default => ['modilon_hospital'],
        };

        $level = $request->input('level', $user->hasAnyRole(['admin', 'procurement_officer']) ? 'corridor' : $defaultLevel);
        if (! in_array($level, $allowedLevels, true)) {
            $level = $allowedLevels[0];
        }

        $from = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->subMonths(LmisService::LOOKBACK_MONTHS)->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfDay();

        if ($level === 'corridor') {
            $rows = LmisService::procurementSuggestions()
                ->map(fn (array $row) => [
                    'key' => 'medicine:'.$row['medicine_id'],
                    'medicine_id' => (int) $row['medicine_id'],
                    'drug_name' => $row['drug_name'],
                    'dosage' => $row['dosage'],
                    'unit' => $row['unit'],
                    'label' => $row['label'],
                    'stock_on_hand' => $row['stock_on_hand'],
                    'consumed' => $row['consumed'],
                    'amc' => $row['amc'],
                    'days_of_stock' => $row['days_of_stock'],
                    'status' => $row['status'],
                    'status_label' => $row['status_label'],
                    'suggested_quantity' => $row['suggested_quantity'],
                    'months_of_cover' => $row['months_of_cover'],
                    'pending_on_order' => $row['pending_on_order'],
                    'reorder_point' => null,
                ]);
            $counts = [
                'stock_out' => $rows->where('status', 'stock_out')->count(),
                'critical' => $rows->where('status', 'critical')->count(),
                'low' => $rows->where('status', 'low')->count(),
                'adequate' => $rows->whereIn('status', ['adequate', 'overstock'])->count(),
            ];
        } else {
            $rows = LmisService::stockStatusForLevel($level, $from, $to);
            $counts = [
                'stock_out' => $rows->where('status', 'stock_out')->count(),
                'critical' => $rows->where('status', 'critical')->count(),
                'low' => $rows->where('status', 'low')->count(),
                'adequate' => $rows->whereIn('status', ['adequate', 'overstock'])->count(),
            ];
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $rows = $rows->where('status', $request->status)->values();
        }

        $levelLabels = [
            'ndoh' => 'NDoH central stock',
            'lae_ams' => 'Lae AMS warehouse',
            'modilon_hospital' => 'Modilon General Hospital',
            'corridor' => 'Madang corridor (all levels)',
        ];

        return view('reports.stock-status', [
            'rows' => $rows,
            'counts' => $counts,
            'level' => $level,
            'allowedLevels' => $allowedLevels,
            'levelLabels' => $levelLabels,
            'from' => $from,
            'to' => $to,
            'statusFilter' => $request->input('status', 'all'),
            'monthsOfCover' => $level === 'modilon_hospital'
                ? LmisService::HOSPITAL_MONTHS_OF_COVER
                : LmisService::PROCUREMENT_MONTHS_OF_COVER,
        ]);
    }
}
