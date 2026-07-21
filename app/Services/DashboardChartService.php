<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\Order;
use App\Models\StockTransfer;
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
            ],
            'procurement_officer' => [
                self::orderStatusChart($userId),
                self::ordersTrendChart($userId),
            ],
            'store_manager' => [
                self::inventoryHealthChart($inventoryLevel ?? 'lae_ams'),
            ],
            'pharmacy_manager', 'pharmacist' => [
                self::inventoryHealthChart($inventoryLevel ?? 'modilon_hospital'),
            ],
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

        $labels = ['Pending', 'Ordered', 'Shipped', 'Partial', 'Received', 'Cancelled'];
        $keys = ['pending', 'ordered', 'shipped', 'partial', 'received', 'cancelled'];
        $data = array_map(fn (string $key) => (int) ($counts[$key] ?? 0), $keys);

        return [
            'id' => 'order-status-'.($userId ?? 'all'),
            'type' => 'doughnut',
            'title' => $userId ? 'My orders by status' : 'Orders by status',
            'subtitle' => 'Current procurement pipeline',
            'labels' => $labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => ['#f59e0b', '#3b82f6', '#8b5cf6', '#eab308', '#10b981', '#ef4444'],
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

        return [
            'id' => 'orders-trend-'.($userId ?? 'all'),
            'type' => 'bar',
            'title' => $userId ? 'My order activity' : 'Order activity',
            'subtitle' => 'Last 6 months',
            'labels' => $labels,
            'datasets' => [[
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

        return [
            'id' => 'procurement-spend-'.$userId,
            'type' => 'line',
            'title' => 'Procurement spend (PGK)',
            'subtitle' => 'Invoice totals from your orders',
            'labels' => $labels,
            'datasets' => [[
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

        $labels = ['In transit by road', 'Received', 'Cancelled'];
        $keys = ['sent', 'received', 'cancelled'];
        $data = array_map(fn (string $key) => (int) ($counts[$key] ?? 0), $keys);

        return [
            'id' => 'shipment-status-'.($toLevel ?? 'all'),
            'type' => 'bar',
            'title' => 'Road delivery status',
            'subtitle' => $toLevel ? 'Lae AMS warehouse deliveries' : 'NDoH → Lae AMS by road',
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Road deliveries',
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
                'labels' => ['No stock data'],
                'datasets' => [[
                    'label' => 'Units',
                    'data' => [0],
                    'backgroundColor' => '#94a3b8',
                    'borderRadius' => 8,
                ]],
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
}
