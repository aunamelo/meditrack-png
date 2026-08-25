<?php

namespace App\Services;

use App\Models\DispensingRecord;
use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardChartService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function forRole(string $roleKey, ?int $userId = null, ?string $inventoryLevel = null): array
    {
        return match ($roleKey) {
            'admin' => [
                self::orderStatusChart(),
                self::ordersTrendChart(),
                self::shipmentStatusChart('lae_ams'),
            ],
            'procurement_officer' => array_values(array_filter([
                self::orderStatusChart($userId),
                self::ordersTrendChart($userId),
                $userId ? self::procurementSpendChart($userId) : null,
            ])),
            'store_manager' => [
                self::shipmentStatusChart('lae_ams'),
            ],
            'pharmacy_manager', 'pharmacist' => [],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function orderStatusChart(?int $userId = null): array
    {
        $query = Order::query();

        if ($userId) {
            $query->where('created_by', $userId);
        }

        $counts = $query
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = ['Pending', 'Manufacturing', 'In transit', 'Customs', 'FX cleared', 'Partial', 'Received', 'Cancelled'];
        $keys = ['pending', 'manufacturing', 'shipped', 'customs', 'fx_cleared', 'partial', 'received', 'cancelled'];
        $data = array_map(fn (string $key) => (int) ($counts[$key] ?? 0), $keys);

        $empty = array_sum($data) === 0;

        return [
            'id' => 'order-status-'.($userId ?? 'all'),
            'type' => 'doughnut',
            'title' => $userId ? 'My orders by status' : 'Orders by status',
            'subtitle' => 'Import pipeline and receipt status',
            'empty' => $empty,
            'empty_message' => $userId
                ? 'Create a procurement order to populate this chart.'
                : 'Orders will appear here once procurement activity starts.',
            'labels' => $empty ? [] : $labels,
            'datasets' => $empty ? [] : [[
                'data' => $data,
                'backgroundColor' => ['#f59e0b', '#3b82f6', '#8b5cf6', '#f97316', '#14b8a6', '#eab308', '#10b981', '#ef4444'],
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function ordersTrendChart(?int $userId = null, int $months = 6): array
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        $query = Order::query()
            ->where('created_at', '>=', $start);

        if ($userId) {
            $query->where('created_by', $userId);
        }

        $rows = $query
            ->get(['created_at'])
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m'))
            ->map->count();

        $labels = [];
        $data = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        $empty = array_sum($data) === 0;

        return [
            'id' => 'orders-trend-'.($userId ?? 'all'),
            'type' => 'bar',
            'title' => $userId ? 'My order activity' : 'Order activity',
            'subtitle' => 'Last 6 months',
            'empty' => $empty,
            'empty_message' => $userId
                ? 'Create a procurement order to populate this chart.'
                : 'Orders will appear here once procurement activity starts.',
            'labels' => $empty ? [] : $labels,
            'datasets' => $empty ? [] : [[
                'label' => 'Orders',
                'data' => $data,
                'backgroundColor' => '#0f766e',
                'borderRadius' => 8,
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function procurementSpendChart(?int $userId): array
    {
        $start = now()->subMonths(5)->startOfMonth();

        $rows = Order::query()
            ->where('created_by', $userId)
            ->where('created_at', '>=', $start)
            ->whereNotNull('invoice_amount')
            ->get(['created_at', 'invoice_amount'])
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m'))
            ->map(fn ($orders) => $orders->sum('invoice_amount'));

        $labels = [];
        $data = [];

        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = round((float) ($rows[$key] ?? 0), 2);
        }

        $empty = array_sum($data) === 0;

        return [
            'id' => 'procurement-spend-'.$userId,
            'type' => 'line',
            'title' => 'Procurement spend (PGK)',
            'subtitle' => 'Invoice totals from your orders',
            'empty' => $empty,
            'empty_message' => 'Invoice amounts will appear here once orders are recorded.',
            'labels' => $empty ? [] : $labels,
            'datasets' => $empty ? [] : [[
                'label' => 'Kina (PGK)',
                'data' => $data,
                'borderColor' => '#0f766e',
                'backgroundColor' => 'rgba(15, 118, 110, 0.15)',
                'fill' => true,
                'tension' => 0.35,
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function inventoryHealthChart(string $level): array
    {
        $base = Drug::atLevel($level);

        $active = (clone $base)->inInventory()
            ->whereColumn('quantity_on_hand', '>', 'reorder_point')
            ->where('expiry_date', '>', now()->addMonths(6))
            ->count();
        $low = (clone $base)->inInventory()->lowStock()->count();
        $expiring = (clone $base)->inInventory()->expiring()->count();
        $expired = (clone $base)->expired()->count();

        return [
            'id' => 'inventory-health-'.$level,
            'type' => 'doughnut',
            'title' => 'Inventory health',
            'subtitle' => 'Stock condition overview',
            'labels' => ['Healthy', 'Low stock', 'Expiring soon', 'Expired'],
            'datasets' => [[
                'data' => [$active, $low, $expiring, $expired],
                'backgroundColor' => ['#0f766e', '#f59e0b', '#f97316', '#ef4444'],
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function shipmentStatusChart(?string $toLevel = null): array
    {
        $query = StockTransfer::query();

        if ($toLevel) {
            $query->toLevel($toLevel);
        }

        $counts = $query
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = $toLevel === 'lae_ams'
            ? ['Shipped to Lae AMS', 'Received at Lae AMS', 'Cancelled']
            : ['In transit by road', 'Received', 'Cancelled'];
        $keys = ['sent', 'received', 'cancelled'];
        $data = array_map(fn (string $key) => (int) ($counts[$key] ?? 0), $keys);
        $empty = array_sum($data) === 0;

        return [
            'id' => 'shipment-status-'.($toLevel ?? 'all'),
            'type' => 'bar',
            'title' => $toLevel === 'lae_ams' ? 'NDoH shipment status' : 'Road delivery status',
            'subtitle' => $toLevel === 'lae_ams' ? 'NDoH → Lae AMS logistics' : 'NDoH → Lae AMS shipments',
            'empty' => $empty,
            'empty_message' => $toLevel === 'lae_ams'
                ? 'Shipments from NDoH will appear here once they are sent.'
                : 'Road deliveries will appear here once they are created.',
            'labels' => $empty ? [] : $labels,
            'datasets' => $empty ? [] : [[
                'label' => $toLevel === 'lae_ams' ? 'Shipments' : 'Road deliveries',
                'data' => $data,
                'backgroundColor' => ['#3b82f6', '#10b981', '#ef4444'],
                'borderRadius' => 8,
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function topStockChart(string $level, int $limit = 5): array
    {
        $drugs = Drug::query()
            ->atLevel($level)
            ->inInventory()
            ->orderByDesc('quantity_on_hand')
            ->limit($limit)
            ->get(['drug_name', 'dosage', 'quantity_on_hand']);

        if ($drugs->isEmpty()) {
            return [
                'id' => 'top-stock-'.$level,
                'type' => 'bar',
                'title' => 'Top stock levels',
                'subtitle' => 'Highest quantity on hand',
                'empty' => true,
                'empty_message' => 'Receive inventory to populate this chart.',
                'labels' => [],
                'datasets' => [],
                'horizontal' => true,
            ];
        }

        return [
            'id' => 'top-stock-'.$level,
            'type' => 'bar',
            'title' => 'Top stock levels',
            'subtitle' => 'Highest quantity on hand',
            'labels' => $drugs->map(fn (Drug $drug) => $drug->drug_name.' ('.$drug->dosage.')')->all(),
            'datasets' => [[
                'label' => 'Units on hand',
                'data' => $drugs->pluck('quantity_on_hand')->map(fn ($qty) => (int) $qty)->all(),
                'backgroundColor' => '#56b5ab',
                'borderRadius' => 8,
            ]],
            'horizontal' => true,
        ];
    }

    /**
     * Role-aware Supply overview payload (stock, donut, flow, activity, dispensing).
     *
     * @return array<string, mixed>
     */
    public static function supplyOverview(string $roleKey, ?string $inventoryLevel = null, ?int $userId = null): array
    {
        $level = $inventoryLevel ?? match ($roleKey) {
            'store_manager' => 'lae_ams',
            'pharmacy_manager', 'pharmacist' => 'modilon_hospital',
            default => 'ndoh',
        };

        $showFlow = in_array($roleKey, ['admin', 'procurement_officer', 'store_manager'], true);
        $showDispensing = in_array($roleKey, ['admin', 'procurement_officer', 'pharmacy_manager', 'pharmacist'], true);
        $showActivity = in_array($roleKey, ['admin', 'procurement_officer'], true);
        $dispensingRoute = getDashboardRoutePrefix().'charts.dispensing';
        $dispensingUrl = $showDispensing && \Illuminate\Support\Facades\Route::has($dispensingRoute)
            ? route($dispensingRoute)
            : null;

        return [
            'stockChart' => self::stockVsReorderChart($level),
            'statusDonut' => self::stockStatusDonut($level),
            'activityChart' => $showActivity ? self::roleActivityChart() : null,
            'flow' => $showFlow ? self::supplyFlow($roleKey) : null,
            'dispensing' => $showDispensing ? self::dispensingTrendChart(null, 30) : null,
            'dispensingUrl' => $dispensingUrl,
            'dispensingDrugs' => $showDispensing ? self::dispensingDrugOptions() : [],
        ];
    }

    /**
     * On-hand vs reorder point, grouped by medicine at a facility.
     *
     * @return array<string, mixed>
     */
    public static function stockVsReorderChart(string $level, int $limit = 12): array
    {
        $rows = Drug::query()
            ->atLevel($level)
            ->inInventory()
            ->selectRaw('drug_name, dosage, SUM(quantity_on_hand) as qty, MIN(reorder_point) as reorder_point')
            ->groupBy('drug_name', 'dosage')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();

        $levelLabel = match ($level) {
            'ndoh' => 'NDoH Central Store',
            'lae_ams' => 'Lae AMS',
            'modilon_hospital' => 'Modilon',
            default => 'Facility',
        };

        if ($rows->isEmpty()) {
            return [
                'id' => 'stock-reorder-'.$level,
                'type' => 'bar',
                'title' => 'Stock levels',
                'subtitle' => $levelLabel.' · on hand vs reorder point',
                'empty' => true,
                'empty_message' => 'Receive inventory to populate this chart.',
                'labels' => [],
                'datasets' => [],
            ];
        }

        return [
            'id' => 'stock-reorder-'.$level,
            'type' => 'bar',
            'title' => 'Stock levels',
            'subtitle' => $levelLabel.' · on hand vs reorder threshold',
            'labels' => $rows->map(fn ($row) => trim($row->drug_name.($row->dosage ? ' ('.$row->dosage.')' : '')))->all(),
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => 'On hand',
                    'data' => $rows->map(fn ($row) => (int) $row->qty)->all(),
                    'backgroundColor' => '#0f766e',
                    'borderRadius' => 6,
                    'order' => 2,
                ],
                [
                    'type' => 'line',
                    'label' => 'Reorder point',
                    'data' => $rows->map(fn ($row) => (int) $row->reorder_point)->all(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => '#f59e0b',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'tension' => 0,
                    'order' => 1,
                ],
            ],
        ];
    }

    /**
     * Adequate / low / critical / expired donut using LMIS + expired batches.
     *
     * @return array<string, mixed>
     */
    public static function stockStatusDonut(string $level): array
    {
        $counts = LmisService::statusCountsForLevel($level);
        $expired = Drug::query()->atLevel($level)->inInventory()->expired()->count();
        $adequate = (int) ($counts['adequate'] ?? 0);
        $low = (int) ($counts['low'] ?? 0);
        $critical = (int) ($counts['critical'] ?? 0) + (int) ($counts['stock_out'] ?? 0);
        $data = [$adequate, $low, $critical, $expired];
        $empty = array_sum($data) === 0;

        return [
            'id' => 'stock-status-'.$level,
            'type' => 'doughnut',
            'title' => 'Stock status',
            'subtitle' => 'Adequate / low / critical / expired',
            'empty' => $empty,
            'empty_message' => 'Receive inventory to populate this chart.',
            'labels' => $empty ? [] : ['Adequate', 'Low', 'Critical', 'Expired'],
            'datasets' => $empty ? [] : [[
                'data' => $data,
                'backgroundColor' => ['#0f766e', '#f59e0b', '#ea580c', '#ef4444'],
            ]],
        ];
    }

    /**
     * Corridor volume for the last 90 days.
     *
     * @return array<string, mixed>
     */
    public static function supplyFlow(string $roleKey = 'admin'): array
    {
        $from = now()->subDays(90)->startOfDay();

        $intoNdoh = (int) OrderItem::query()
            ->where('quantity_received', '>', 0)
            ->whereHas('order', fn ($query) => $query->where('updated_at', '>=', $from))
            ->sum('quantity_received');

        $toLae = (int) StockTransfer::query()
            ->fromLevel('ndoh')
            ->toLevel('lae_ams')
            ->whereNull('hospital_order_id')
            ->whereIn('status', ['sent', 'received'])
            ->where(function ($query) use ($from) {
                $query->whereDate('sent_date', '>=', $from)
                    ->orWhere('created_at', '>=', $from);
            })
            ->sum('quantity_sent');

        $toModilon = (int) StockTransfer::query()
            ->toLevel('modilon_hospital')
            ->whereIn('status', ['sent', 'received'])
            ->where(function ($query) use ($from) {
                $query->whereDate('sent_date', '>=', $from)
                    ->orWhere('created_at', '>=', $from);
            })
            ->sum('quantity_sent');

        $dispensed = (int) DispensingRecord::query()
            ->where('dispensed_at', '>=', $from)
            ->sum('quantity_dispensed');

        $nodes = [
            ['key' => 'ndoh', 'label' => 'NDoH', 'detail' => 'Central store', 'value' => $intoNdoh],
            ['key' => 'lae', 'label' => 'Lae AMS', 'detail' => 'Regional warehouse', 'value' => $toLae],
            ['key' => 'modilon', 'label' => 'Modilon', 'detail' => 'Hospital pharmacy', 'value' => $toModilon],
            ['key' => 'dispense', 'label' => 'Dispensed', 'detail' => 'Patients', 'value' => $dispensed],
        ];

        $max = max(1, $intoNdoh, $toLae, $toModilon, $dispensed);

        return [
            'title' => 'Supply chain flow',
            'subtitle' => $roleKey === 'store_manager'
                ? 'Units moving through Lae AMS · last 90 days'
                : 'NDoH → Lae AMS → Modilon → dispensing · last 90 days',
            'nodes' => $nodes,
            'max' => $max,
            'empty' => $intoNdoh + $toLae + $toModilon + $dispensed === 0,
        ];
    }

    /**
     * Actions in the last 14 days grouped by portal role.
     *
     * @return array<string, mixed>
     */
    public static function roleActivityChart(): array
    {
        $from = now()->subDays(14)->startOfDay();

        $events = collect()
            ->concat(Order::query()->where('created_at', '>=', $from)->pluck('created_by'))
            ->concat(StockTransfer::query()->where('created_at', '>=', $from)->pluck('sent_by'))
            ->concat(HospitalOrder::query()->where('created_at', '>=', $from)->pluck('requested_by'))
            ->concat(HospitalOrder::query()->where('reviewed_at', '>=', $from)->pluck('reviewed_by'))
            ->concat(DispensingRecord::query()->where('dispensed_at', '>=', $from)->pluck('dispensed_by'))
            ->concat(StockAdjustment::query()->where('adjusted_at', '>=', $from)->pluck('adjusted_by'))
            ->filter()
            ->map(fn ($id) => (int) $id);

        $roleCounts = [
            'admin' => 0,
            'procurement_officer' => 0,
            'store_manager' => 0,
            'pharmacy_manager' => 0,
            'pharmacist' => 0,
        ];

        if ($events->isNotEmpty()) {
            $users = User::query()
                ->with('roles')
                ->whereIn('id', $events->unique()->values())
                ->get()
                ->keyBy('id');

            foreach ($events as $userId) {
                $key = $users->get($userId)?->portalRoleKey();
                if ($key && isset($roleCounts[$key])) {
                    $roleCounts[$key]++;
                }
            }
        }

        return [
            'id' => 'role-activity',
            'type' => 'bar',
            'title' => 'Activity by role',
            'subtitle' => 'Orders, shipments, stock takes and dispenses · last 14 days',
            'labels' => ['NDoH Admin', 'Procurement', 'Store Manager', 'Pharmacy Mgr', 'Pharmacist'],
            'datasets' => [[
                'label' => 'Actions',
                'data' => array_values($roleCounts),
                'backgroundColor' => ['#0f766e', '#14b8a6', '#3b82f6', '#8b5cf6', '#f59e0b'],
                'borderRadius' => 8,
            ]],
        ];
    }

    /**
     * Daily dispensed quantity, optional medicine name filter.
     *
     * @return array<string, mixed>
     */
    public static function dispensingTrendChart(?string $drugName = null, int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $query = DispensingRecord::query()
            ->where('dispensed_at', '>=', $start)
            ->with('drug:id,drug_name,dosage');

        if (filled($drugName)) {
            $query->whereHas('drug', function ($drugQuery) use ($drugName) {
                $drugQuery->where('drug_name', $drugName);
            });
        }

        $rows = $query->get(['dispensed_at', 'quantity_dispensed', 'drug_id'])
            ->groupBy(fn (DispensingRecord $record) => $record->dispensed_at->format('Y-m-d'))
            ->map(fn ($day) => (int) $day->sum('quantity_dispensed'));

        $labels = [];
        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $labels[] = $day->format('j M');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        $subtitle = filled($drugName)
            ? $drugName.' · last '.$days.' days'
            : 'All medicines · last '.$days.' days';

        return [
            'id' => 'dispensing-trend',
            'type' => 'line',
            'title' => 'Dispensing trends',
            'subtitle' => $subtitle,
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Units dispensed',
                'data' => $data,
                'borderColor' => '#0f766e',
                'backgroundColor' => 'rgba(15, 118, 110, 0.12)',
                'fill' => true,
                'tension' => 0.35,
            ]],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function dispensingDrugOptions(): array
    {
        return DispensingRecord::query()
            ->with('drug:id,drug_name')
            ->latest('dispensed_at')
            ->limit(200)
            ->get()
            ->pluck('drug.drug_name')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
