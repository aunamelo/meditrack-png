<?php

namespace App\Services;

use App\Models\DispensingRecord;
use App\Models\Drug;
use App\Models\HospitalOrder;
use Illuminate\Support\Collection;

/**
 * Interactive dashboard insight panels (stock health bars, at-risk list, expiry timeline).
 */
class DashboardInsightService
{
    private const COVER_TARGET_DAYS = 90;

    /**
     * @return array<string, mixed>
     */
    public static function forRole(string $roleKey, ?string $inventoryLevel = null, ?int $userId = null): array
    {
        return match ($roleKey) {
            'admin', 'procurement_officer' => [
                'stockHealth' => self::corridorStockHealth(8),
                'atRisk' => self::corridorAtRisk(8),
                'expiry' => self::expiryTimeline($inventoryLevel ?? 'ndoh', 8),
                'dispenseTrend' => null,
                'shipments' => self::roadShipmentTimeline(),
            ],
            'store_manager' => [
                'stockHealth' => self::levelStockHealth($inventoryLevel ?? 'lae_ams', 8),
                'atRisk' => self::levelAtRisk($inventoryLevel ?? 'lae_ams', 8),
                'expiry' => self::expiryTimeline($inventoryLevel ?? 'lae_ams', 8),
                'dispenseTrend' => null,
                'shipments' => self::roadShipmentTimeline(),
            ],
            'pharmacy_manager' => [
                'stockHealth' => self::levelStockHealth($inventoryLevel ?? 'modilon_hospital', 8),
                'atRisk' => self::levelAtRisk($inventoryLevel ?? 'modilon_hospital', 8),
                'expiry' => self::expiryTimeline($inventoryLevel ?? 'modilon_hospital', 8),
                'dispenseTrend' => null,
                'shipments' => self::roadShipmentTimeline($userId),
            ],
            'pharmacist' => [
                'stockHealth' => self::levelStockHealth($inventoryLevel ?? 'modilon_hospital', 6),
                'atRisk' => self::levelAtRisk($inventoryLevel ?? 'modilon_hospital', 6),
                'expiry' => self::expiryTimeline($inventoryLevel ?? 'modilon_hospital', 6),
                'dispenseTrend' => $userId ? self::dispenseTrend($userId, 14) : null,
                'shipments' => self::roadShipmentTimeline(),
            ],
            default => [
                'stockHealth' => null,
                'atRisk' => null,
                'expiry' => null,
                'dispenseTrend' => null,
                'shipments' => null,
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function levelStockHealth(string $level, int $limit = 8): array
    {
        $rows = LmisService::stockStatusForLevel($level);
        $levelLabel = match ($level) {
            'ndoh' => 'NDoH',
            'lae_ams' => 'Lae AMS',
            'modilon_hospital' => 'Modilon',
            default => 'Facility',
        };

        return self::stockHealthPanel(
            title: "{$levelLabel} stock cover",
            subtitle: 'Days of stock from recent consumption (click for Stock Status)',
            rows: $rows,
            limit: $limit,
            moreUrl: getDashboardStockStatusRoute('index').'?level='.$level,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function corridorStockHealth(int $limit = 8): array
    {
        $rows = LmisService::procurementSuggestions()->map(fn (array $row) => [
            'label' => $row['label'],
            'drug_name' => $row['drug_name'],
            'dosage' => $row['dosage'],
            'stock_on_hand' => $row['stock_on_hand'],
            'amc' => $row['amc'],
            'days_of_stock' => $row['days_of_stock'],
            'status' => $row['status'],
            'status_label' => $row['status_label'],
            'unit' => $row['unit'],
        ]);

        return self::stockHealthPanel(
            title: 'Corridor stock cover',
            subtitle: 'Madang corridor days of stock vs consumption',
            rows: $rows,
            limit: $limit,
            moreUrl: getDashboardStockStatusRoute('index').'?level=corridor',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function levelAtRisk(string $level, int $limit = 8): array
    {
        $rows = LmisService::stockStatusForLevel($level);
        $levelParam = $level;

        return self::atRiskPanel(
            title: 'Medicines at risk',
            subtitle: 'Stock-out, critical, or low cover',
            rows: $rows,
            limit: $limit,
            moreUrl: getDashboardStockStatusRoute('index').'?level='.$levelParam,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function corridorAtRisk(int $limit = 8): array
    {
        $rows = LmisService::procurementSuggestions()->map(fn (array $row) => [
            'label' => $row['label'],
            'stock_on_hand' => $row['stock_on_hand'],
            'days_of_stock' => $row['days_of_stock'],
            'status' => $row['status'],
            'status_label' => $row['status_label'],
            'suggested_quantity' => $row['suggested_quantity'],
            'unit' => $row['unit'],
            'amc' => $row['amc'],
        ]);

        return self::atRiskPanel(
            title: 'Procurement priorities',
            subtitle: 'Medicines needing national restock',
            rows: $rows,
            limit: $limit,
            moreUrl: getDashboardStockStatusRoute('index').'?level=corridor',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function expiryTimeline(string $level, int $limit = 8): array
    {
        $batches = Drug::query()
            ->atLevel($level)
            ->inInventory()
            ->where('quantity_on_hand', '>', 0)
            ->where('expiry_date', '<=', now()->addMonths(6))
            ->orderBy('expiry_date')
            ->limit($limit)
            ->get(['id', 'drug_name', 'dosage', 'batch_number', 'expiry_date', 'quantity_on_hand', 'unit']);

        $items = $batches->map(function (Drug $drug) {
            $daysLeft = (int) now()->startOfDay()->diffInDays($drug->expiry_date->copy()->startOfDay(), false);
            $urgency = match (true) {
                $daysLeft < 0 => 'expired',
                $daysLeft <= 30 => 'urgent',
                $daysLeft <= 90 => 'soon',
                default => 'watch',
            };

            // Fill bar empties as expiry approaches (180-day window).
            $window = 180;
            $remaining = max(0, min($window, $daysLeft));
            $percent = (int) round(($remaining / $window) * 100);

            return [
                'label' => $drug->drug_name.($drug->dosage ? ' ('.$drug->dosage.')' : ''),
                'batch' => $drug->batch_number,
                'qty' => number_format($drug->quantity_on_hand).' '.($drug->unit ?? ''),
                'expiry' => $drug->expiry_date->format('d M Y'),
                'days_left' => $daysLeft,
                'days_label' => $daysLeft < 0
                    ? 'Expired'
                    : ($daysLeft === 0 ? 'Expires today' : $daysLeft.' days left'),
                'urgency' => $urgency,
                'percent' => $percent,
                'url' => getDashboardDrugRoute('show', $drug),
            ];
        })->all();

        return [
            'title' => 'Expiry timeline',
            'subtitle' => 'Batches expiring within 6 months — prefer FEFO',
            'items' => $items,
            'more_url' => getDashboardDrugRoute('index').'?status=expiring_soon',
            'empty' => $items === [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function dispenseTrend(int $userId, int $days = 14): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $counts = DispensingRecord::query()
            ->where('dispensed_by', $userId)
            ->where('dispensed_at', '>=', $start)
            ->get(['dispensed_at'])
            ->groupBy(fn (DispensingRecord $r) => $r->dispensed_at->format('Y-m-d'))
            ->map->count();

        $points = [];
        $max = 1;
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->format('Y-m-d');
            $value = (int) ($counts[$key] ?? 0);
            $max = max($max, $value);
            $points[] = [
                'label' => $day->format('D j'),
                'value' => $value,
                'is_today' => $day->isToday(),
            ];
        }

        foreach ($points as &$point) {
            $point['percent'] = (int) round(($point['value'] / $max) * 100);
        }
        unset($point);

        $total = array_sum(array_column($points, 'value'));

        return [
            'title' => 'My dispensing trend',
            'subtitle' => "Last {$days} days · {$total} dispenses",
            'points' => $points,
            'more_url' => getDashboardDispensingRoute('index'),
        ];
    }

    /**
     * Lae AMS → Modilon road deliveries (ordered / in transit / received).
     *
     * @return array<string, mixed>
     */
    public static function roadShipmentTimeline(?int $requesterId = null, int $limit = 8): array
    {
        $query = HospitalOrder::query()
            ->with(['stockTransfer.vehicle', 'requester'])
            ->whereIn('status', ['pending', 'approved', 'shipped', 'received'])
            ->latest();

        if ($requesterId) {
            $query->where('requested_by', $requesterId);
        }

        $canOpenOrder = auth()->user()?->hasAnyRole(['store_manager', 'pharmacy_manager']) ?? false;

        $items = $query->limit($limit)->get()->map(function (HospitalOrder $order) use ($canOpenOrder) {
            $stage = match ($order->status) {
                'shipped' => 'in_transit',
                'received' => 'received',
                default => 'ordered',
            };

            $transfer = $order->stockTransfer;

            return [
                'title' => $order->order_number,
                'subtitle' => $order->medicinesLabel(),
                'meta' => match ($stage) {
                    'in_transit' => 'In transit',
                    'received' => 'Received',
                    default => 'Ordered',
                },
                'stage' => $stage,
                'when' => optional($transfer?->sent_date ?? $order->created_at)->format('d M Y'),
                'vehicle' => $transfer?->vehicle?->displayLabel(),
                'url' => $canOpenOrder ? getDashboardHospitalOrderRoute('show', $order) : null,
            ];
        })->all();

        $moreUrl = null;
        if (auth()->user()?->hasAnyRole(['store_manager', 'pharmacy_manager'])) {
            $moreUrl = getDashboardHospitalShipmentRoute('index');
        }

        return [
            'title' => 'Lae AMS → Modilon shipments',
            'subtitle' => 'Ordered · in transit · received',
            'items' => $items,
            'more_url' => $moreUrl,
            'empty' => $items === [],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    protected static function stockHealthPanel(string $title, string $subtitle, Collection $rows, int $limit, string $moreUrl): array
    {
        $sorted = $rows
            ->sortBy([
                fn ($row) => self::statusRank($row['status'] ?? 'adequate'),
                fn ($row) => $row['days_of_stock'] ?? 9999,
                fn ($row) => $row['label'] ?? $row['drug_name'] ?? '',
            ])
            ->take($limit)
            ->values();

        $items = $sorted->map(function (array $row) use ($moreUrl) {
            $dos = $row['days_of_stock'];
            $status = $row['status'] ?? 'adequate';
            $percent = self::coverPercent($dos, $status);

            return [
                'label' => $row['label'] ?? trim(($row['drug_name'] ?? '').(isset($row['dosage']) && $row['dosage'] ? ' ('.$row['dosage'].')' : '')),
                'status' => $status,
                'status_label' => $row['status_label'] ?? LmisService::statusLabel($status),
                'tone' => LmisService::statusTone($status),
                'days_label' => $dos === null
                    ? 'No recent use'
                    : ($dos <= 0 ? '0 days' : number_format((float) $dos, 0).' days'),
                'stock_label' => number_format((int) ($row['stock_on_hand'] ?? 0)).' '.($row['unit'] ?? 'units'),
                'percent' => $percent,
                'url' => $moreUrl,
            ];
        })->all();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'items' => $items,
            'more_url' => $moreUrl,
            'empty' => $items === [],
            'target_days' => self::COVER_TARGET_DAYS,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    protected static function atRiskPanel(string $title, string $subtitle, Collection $rows, int $limit, string $moreUrl): array
    {
        $items = $rows
            ->filter(fn (array $row) => in_array($row['status'] ?? '', ['stock_out', 'critical', 'low'], true))
            ->sortBy([
                fn ($row) => self::statusRank($row['status'] ?? 'adequate'),
                fn ($row) => $row['days_of_stock'] ?? 0,
            ])
            ->take($limit)
            ->values()
            ->map(function (array $row) use ($moreUrl) {
                $dos = $row['days_of_stock'];
                $status = $row['status'];

                return [
                    'label' => $row['label'] ?? trim(($row['drug_name'] ?? '').(isset($row['dosage']) && $row['dosage'] ? ' ('.$row['dosage'].')' : '')),
                    'status' => $status,
                    'status_label' => $row['status_label'] ?? LmisService::statusLabel($status),
                    'tone' => LmisService::statusTone($status),
                    'detail' => ($dos === null ? 'No AMC' : number_format((float) $dos, 0).' days cover')
                        .' · '.number_format((int) ($row['stock_on_hand'] ?? 0)).' on hand'
                        .(isset($row['suggested_quantity']) && $row['suggested_quantity'] > 0
                            ? ' · suggest '.number_format((int) $row['suggested_quantity'])
                            : ''),
                    'url' => $moreUrl.(str_contains($moreUrl, '?') ? '&' : '?').'status='.$status,
                ];
            })
            ->all();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'items' => $items,
            'more_url' => $moreUrl,
            'empty' => $items === [],
            'count' => count($items),
        ];
    }

    protected static function statusRank(string $status): int
    {
        return match ($status) {
            'stock_out' => 0,
            'critical' => 1,
            'low' => 2,
            'adequate' => 3,
            'overstock' => 4,
            default => 5,
        };
    }

    protected static function coverPercent(?float $daysOfStock, string $status): int
    {
        if ($status === 'stock_out' || ($daysOfStock !== null && $daysOfStock <= 0)) {
            return 4;
        }

        if ($daysOfStock === null) {
            return 70;
        }

        return (int) max(4, min(100, round(($daysOfStock / self::COVER_TARGET_DAYS) * 100)));
    }
}
