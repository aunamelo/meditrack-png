<?php

namespace App\Services;

use App\Models\DispensingRecord;
use App\Models\Drug;
use App\Models\Medicine;
use App\Models\StockTransfer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Lightweight LMIS-inspired analytics for MediTrack Phase 1.
 *
 * Uses dispensing (Modilon) and facility issues (stock transfers) as consumption,
 * then derives days of stock and suggested order quantities. This complements
 * e-LMIS conceptually; it does not replace the national e-LMIS.
 */
class LmisService
{
    public const LOOKBACK_MONTHS = 3;

    public const HOSPITAL_MONTHS_OF_COVER = 3;

    public const PROCUREMENT_MONTHS_OF_COVER = 6;

    public const CRITICAL_DAYS = 14;

    public const LOW_DAYS = 30;

    /**
     * Stock status rows for one facility level.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function stockStatusForLevel(string $level, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $from ??= now()->subMonths(self::LOOKBACK_MONTHS)->startOfDay();
        $to ??= now()->endOfDay();
        $months = max(1.0, $from->diffInDays($to) / 30);

        $stockRows = Drug::query()
            ->atLevel($level)
            ->inInventory()
            ->select([
                'medicine_id',
                'drug_name',
                'dosage',
                'unit',
                DB::raw('SUM(quantity_on_hand) as stock_on_hand'),
                DB::raw('MIN(reorder_point) as reorder_point'),
            ])
            ->groupBy('medicine_id', 'drug_name', 'dosage', 'unit')
            ->get()
            ->keyBy(fn ($row) => self::itemKey($row->medicine_id, $row->drug_name, $row->dosage));

        $consumption = self::consumptionByKey($level, $from, $to);
        $keys = collect($stockRows->keys())->merge(array_keys($consumption))->unique()->values();

        $monthsOfCover = $level === 'modilon_hospital'
            ? self::HOSPITAL_MONTHS_OF_COVER
            : self::PROCUREMENT_MONTHS_OF_COVER;

        return $keys->map(function (string $key) use ($stockRows, $consumption, $months, $monthsOfCover) {
            $row = $stockRows->get($key);
            $consumed = (int) ($consumption[$key] ?? 0);
            if ($row && $consumed === 0) {
                $consumed = (int) ($consumption[self::itemKey(null, $row->drug_name, $row->dosage)] ?? 0);
            }
            $amc = $consumed / $months;
            $soh = (int) ($row->stock_on_hand ?? 0);

            if (! $row && $consumed === 0) {
                return null;
            }

            // Resolve name/dosage from stock row, or from a matching drug when only consumption remains.
            $drugName = $row->drug_name ?? null;
            $dosage = $row->dosage ?? null;
            $medicineId = $row->medicine_id ?? null;
            $unit = $row->unit ?? 'units';
            $reorderPoint = (int) ($row->reorder_point ?? 0);

            if (! $drugName) {
                $parts = self::parseNameKey($key);
                $drugName = $parts['drug_name'] ?? 'Unknown';
                $dosage = $parts['dosage'] ?? '';
                if (str_starts_with($key, 'medicine:')) {
                    $medicineId = (int) substr($key, 9);
                    $medicine = Medicine::query()->find($medicineId);
                    if ($medicine) {
                        $drugName = $medicine->name;
                        $dosage = $medicine->dosage;
                        $unit = $medicine->unit ?? 'units';
                    }
                }
            }

            $dos = self::daysOfStock($soh, $amc);
            $status = self::statusFromDays($soh, $dos);
            $suggested = self::suggestedQuantity($amc, $soh, $monthsOfCover);

            return [
                'key' => $key,
                'medicine_id' => $medicineId,
                'drug_name' => $drugName,
                'dosage' => $dosage,
                'unit' => $unit,
                'label' => trim($drugName.($dosage ? " ({$dosage})" : '')),
                'stock_on_hand' => $soh,
                'consumed' => $consumed,
                'amc' => round($amc, 1),
                'days_of_stock' => $dos,
                'status' => $status,
                'status_label' => self::statusLabel($status),
                'suggested_quantity' => $suggested,
                'months_of_cover' => $monthsOfCover,
                'reorder_point' => $reorderPoint,
            ];
        })->filter()->sortBy('drug_name')->values();
    }

    /**
     * Options for hospital requisition form (Modilon stock status + NDoH catalog).
     * Free-text drug names are not used — every option is a known medicine/batch row.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function hospitalRequisitionOptions(): Collection
    {
        $stockRows = self::stockStatusForLevel('modilon_hospital')
            ->map(function (array $row) {
                $row['source'] = 'stock_status';

                return $row;
            });

        $knownKeys = $stockRows
            ->map(fn (array $row) => strtolower(trim($row['drug_name']).'|'.trim($row['dosage'])))
            ->flip();

        $catalogRows = Medicine::query()
            ->active()
            ->orderBy('name')
            ->orderBy('dosage')
            ->get()
            ->filter(function (Medicine $medicine) use ($knownKeys) {
                $pair = strtolower(trim($medicine->name).'|'.trim($medicine->dosage));

                return ! $knownKeys->has($pair);
            })
            ->map(function (Medicine $medicine) {
                $key = self::itemKey($medicine->id, $medicine->name, $medicine->dosage);

                return [
                    'key' => $key,
                    'medicine_id' => $medicine->id,
                    'drug_name' => $medicine->name,
                    'dosage' => $medicine->dosage,
                    'unit' => $medicine->unit ?? 'units',
                    'label' => $medicine->displayLabel(),
                    'stock_on_hand' => 0,
                    'consumed' => 0,
                    'amc' => 0,
                    'days_of_stock' => null,
                    'status' => 'catalog',
                    'status_label' => 'Catalog',
                    'suggested_quantity' => max(1, (int) $medicine->reorder_point),
                    'months_of_cover' => self::HOSPITAL_MONTHS_OF_COVER,
                    'reorder_point' => $medicine->reorder_point,
                    'source' => 'catalog',
                ];
            })
            ->values();

        return $stockRows
            ->concat($catalogRows)
            ->sortBy([
                fn ($row) => match ($row['status']) {
                    'stock_out' => 0,
                    'critical' => 1,
                    'low' => 2,
                    'catalog' => 4,
                    default => 3,
                },
                fn ($row) => $row['drug_name'],
            ])
            ->values();
    }

    /**
     * National procurement suggestions keyed for the order create form.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function procurementSuggestions(): Collection
    {
        $from = now()->subMonths(self::LOOKBACK_MONTHS)->startOfDay();
        $to = now()->endOfDay();
        $months = self::LOOKBACK_MONTHS;

        $medicines = Medicine::query()->active()->orderBy('name')->orderBy('dosage')->get();
        $modilonConsumption = self::consumptionByKey('modilon_hospital', $from, $to);
        $corridorStock = self::corridorStockByMedicine();
        $pendingByMedicine = self::pendingProcurementByMedicine();

        return $medicines->map(function (Medicine $medicine) use ($modilonConsumption, $corridorStock, $pendingByMedicine, $months) {
            $key = self::itemKey($medicine->id, $medicine->name, $medicine->dosage);
            $nameKey = self::itemKey(null, $medicine->name, $medicine->dosage);
            $consumed = (int) ($modilonConsumption[$key] ?? $modilonConsumption[$nameKey] ?? 0);
            $amc = $consumed / $months;
            $soh = (int) ($corridorStock[$medicine->id] ?? 0);
            $pending = (int) ($pendingByMedicine[$medicine->id] ?? 0);
            $suggested = self::suggestedQuantity($amc, $soh, self::PROCUREMENT_MONTHS_OF_COVER, $pending);
            $dos = self::daysOfStock($soh, $amc);
            $status = self::statusFromDays($soh, $dos);

            return [
                'medicine_id' => (string) $medicine->id,
                'label' => $medicine->displayLabel(),
                'drug_name' => $medicine->name,
                'dosage' => $medicine->dosage,
                'unit' => $medicine->unit ?? 'units',
                'stock_on_hand' => $soh,
                'pending_on_order' => $pending,
                'consumed' => $consumed,
                'amc' => round($amc, 1),
                'days_of_stock' => $dos,
                'status' => $status,
                'status_label' => self::statusLabel($status),
                'suggested_quantity' => $suggested,
                'months_of_cover' => self::PROCUREMENT_MONTHS_OF_COVER,
            ];
        })->values();
    }

    /**
     * Summary counts for dashboards.
     *
     * @return array{stock_out: int, critical: int, low: int, adequate: int}
     */
    public static function statusCountsForLevel(string $level): array
    {
        $counts = [
            'stock_out' => 0,
            'critical' => 0,
            'low' => 0,
            'adequate' => 0,
        ];

        foreach (self::stockStatusForLevel($level) as $row) {
            $status = $row['status'];
            if (isset($counts[$status])) {
                $counts[$status]++;
            } elseif ($status === 'overstock') {
                $counts['adequate']++;
            }
        }

        return $counts;
    }

    public static function suggestedQuantity(float $amc, int $stockOnHand, int $monthsOfCover, int $pendingOnOrder = 0): int
    {
        if ($amc <= 0) {
            return 0;
        }

        $target = (int) ceil($amc * $monthsOfCover);
        $need = $target - $stockOnHand - $pendingOnOrder;

        return max(0, $need);
    }

    /**
     * @return array{drug_name: ?string, dosage: ?string}
     */
    protected static function parseNameKey(string $key): array
    {
        if (! str_starts_with($key, 'name:')) {
            return ['drug_name' => null, 'dosage' => null];
        }

        $payload = substr($key, 5);
        [$name, $dosage] = array_pad(explode('|', $payload, 2), 2, null);

        return [
            'drug_name' => $name ? ucwords($name) : null,
            'dosage' => $dosage,
        ];
    }

    public static function daysOfStock(int $stockOnHand, float $amc): ?float
    {
        if ($stockOnHand <= 0) {
            return 0.0;
        }

        if ($amc <= 0) {
            return null;
        }

        return round(($stockOnHand / $amc) * 30, 1);
    }

    public static function statusFromDays(int $stockOnHand, ?float $daysOfStock): string
    {
        if ($stockOnHand <= 0) {
            return 'stock_out';
        }

        if ($daysOfStock === null) {
            return 'adequate';
        }

        if ($daysOfStock <= self::CRITICAL_DAYS) {
            return 'critical';
        }

        if ($daysOfStock <= self::LOW_DAYS) {
            return 'low';
        }

        if ($daysOfStock > self::PROCUREMENT_MONTHS_OF_COVER * 30) {
            return 'overstock';
        }

        return 'adequate';
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'stock_out' => 'Stock-out',
            'critical' => 'Critical (< 2 weeks)',
            'low' => 'Low (< 30 days)',
            'overstock' => 'Overstock',
            default => 'Adequate',
        };
    }

    public static function statusTone(string $status): string
    {
        return match ($status) {
            'stock_out' => 'red',
            'critical' => 'red',
            'low' => 'amber',
            'overstock' => 'blue',
            default => 'teal',
        };
    }

    /**
     * @return array<string, int>
     */
    protected static function consumptionByKey(string $level, Carbon $from, Carbon $to): array
    {
        if ($level === 'modilon_hospital') {
            $rows = DispensingRecord::query()
                ->join('drugs', 'drugs.id', '=', 'dispensing_records.drug_id')
                ->where('drugs.level', 'modilon_hospital')
                ->whereBetween('dispensing_records.dispensed_at', [$from, $to])
                ->groupBy('drugs.medicine_id', 'drugs.drug_name', 'drugs.dosage')
                ->select([
                    'drugs.medicine_id',
                    'drugs.drug_name',
                    'drugs.dosage',
                    DB::raw('SUM(dispensing_records.quantity_dispensed) as total'),
                ])
                ->get();

            return self::keyedTotals($rows);
        }

        $rows = StockTransfer::query()
            ->join('drugs', 'drugs.id', '=', 'stock_transfers.drug_id')
            ->where('stock_transfers.from_level', $level)
            ->whereIn('stock_transfers.status', ['sent', 'received'])
            ->whereBetween('stock_transfers.sent_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('drugs.medicine_id', 'drugs.drug_name', 'drugs.dosage')
            ->select([
                'drugs.medicine_id',
                'drugs.drug_name',
                'drugs.dosage',
                DB::raw('SUM(stock_transfers.quantity_sent) as total'),
            ])
            ->get();

        return self::keyedTotals($rows);
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return array<string, int>
     */
    protected static function keyedTotals(Collection $rows): array
    {
        $totals = [];

        foreach ($rows as $row) {
            $total = (int) $row->total;
            if ($row->medicine_id) {
                $totals[self::itemKey($row->medicine_id, $row->drug_name, $row->dosage)] = $total;
            } else {
                $totals[self::itemKey(null, $row->drug_name, $row->dosage)] = $total;
            }
        }

        return $totals;
    }

    /**
     * Corridor stock (NDoH + Lae AMS + Modilon) keyed by medicine_id.
     *
     * @return array<int, int>
     */
    protected static function corridorStockByMedicine(): array
    {
        return Drug::query()
            ->inInventory()
            ->whereNotNull('medicine_id')
            ->select('medicine_id', DB::raw('SUM(quantity_on_hand) as stock_on_hand'))
            ->groupBy('medicine_id')
            ->pluck('stock_on_hand', 'medicine_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * Open procurement quantities still in the pipeline, keyed by medicine_id.
     *
     * @return array<int, int>
     */
    protected static function pendingProcurementByMedicine(): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->whereNotIn('orders.status', ['received', 'cancelled'])
            ->whereNotNull('order_items.medicine_id')
            ->groupBy('order_items.medicine_id')
            ->select([
                'order_items.medicine_id',
                DB::raw('SUM(GREATEST(order_items.quantity_ordered - COALESCE(order_items.quantity_received, 0), 0)) as pending_qty'),
            ])
            ->pluck('pending_qty', 'medicine_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    protected static function itemKey(int|string|null $medicineId, string $drugName, string $dosage): string
    {
        if ($medicineId) {
            return 'medicine:'.$medicineId;
        }

        return 'name:'.mb_strtolower(trim($drugName)).'|'.mb_strtolower(trim($dosage));
    }
}
