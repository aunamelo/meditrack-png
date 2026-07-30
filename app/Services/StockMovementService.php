<?php

namespace App\Services;

use App\Models\DispensingRecord;
use App\Models\Order;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StockMovementService
{
    /**
     * Build a chronological stock movement ledger for a facility level.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function forLevel(string $level, Carbon $from, Carbon $to, ?string $search = null): Collection
    {
        $movements = collect()
            ->merge(self::inboundReceipts($level, $from, $to))
            ->merge(self::outboundTransfers($level, $from, $to))
            ->merge(self::inboundTransfers($level, $from, $to))
            ->merge(self::dispensing($level, $from, $to))
            ->merge(self::adjustments($level, $from, $to));

        if ($search) {
            $term = mb_strtolower($search);
            $movements = $movements->filter(function (array $row) use ($term) {
                return str_contains(mb_strtolower($row['medicine']), $term)
                    || str_contains(mb_strtolower($row['reference']), $term)
                    || str_contains(mb_strtolower($row['batch'] ?? ''), $term);
            });
        }

        return $movements
            ->sortByDesc(fn (array $row) => $row['occurred_at']->timestamp)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected static function inboundReceipts(string $level, Carbon $from, Carbon $to): Collection
    {
        if ($level !== 'ndoh') {
            return collect();
        }

        return Order::query()
            ->with(['items.medicine'])
            ->whereIn('status', ['received', 'partial'])
            ->whereNotNull('received_at')
            ->whereBetween('received_at', [$from, $to])
            ->get()
            ->flatMap(function (Order $order) {
                return $order->items->map(function ($item) use ($order) {
                    $qty = (int) $item->quantity_received;

                    if ($qty <= 0) {
                        return null;
                    }

                    $label = $item->medicine?->displayLabel() ?? 'Unknown medicine';

                    return [
                        'occurred_at' => $order->received_at,
                        'type' => 'received',
                        'type_label' => 'Procurement received',
                        'direction' => 'in',
                        'quantity' => $qty,
                        'medicine' => $label,
                        'batch' => null,
                        'reference' => $order->order_number,
                        'notes' => $order->supplier,
                    ];
                })->filter();
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected static function outboundTransfers(string $level, Carbon $from, Carbon $to): Collection
    {
        return StockTransfer::query()
            ->with('drug')
            ->where('from_level', $level)
            ->whereIn('status', ['sent', 'received'])
            ->whereBetween('sent_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (StockTransfer $transfer) {
                $drug = $transfer->drug;

                return [
                    'occurred_at' => $transfer->sent_date?->startOfDay() ?? $transfer->created_at,
                    'type' => 'transfer_out',
                    'type_label' => 'Transferred out',
                    'direction' => 'out',
                    'quantity' => (int) $transfer->quantity_sent,
                    'medicine' => $drug ? trim($drug->drug_name.' ('.$drug->dosage.')') : 'Unknown',
                    'batch' => $transfer->batch_number,
                    'reference' => $transfer->transfer_number,
                    'notes' => 'To '.self::levelLabel($transfer->to_level),
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected static function inboundTransfers(string $level, Carbon $from, Carbon $to): Collection
    {
        // NDoH→Lae creates destination stock on ship; hospital receive creates Modilon stock on receive.
        if ($level === 'lae_ams') {
            return StockTransfer::query()
                ->with(['drug', 'destinationDrug'])
                ->where('to_level', 'lae_ams')
                ->whereIn('status', ['sent', 'received'])
                ->whereBetween('sent_date', [$from->toDateString(), $to->toDateString()])
                ->get()
                ->map(function (StockTransfer $transfer) {
                    $drug = $transfer->destinationDrug ?? $transfer->drug;

                    return [
                        'occurred_at' => $transfer->sent_date?->startOfDay() ?? $transfer->created_at,
                        'type' => 'transfer_in',
                        'type_label' => 'Received from NDoH',
                        'direction' => 'in',
                        'quantity' => (int) $transfer->quantity_sent,
                        'medicine' => $drug ? trim($drug->drug_name.' ('.$drug->dosage.')') : 'Unknown',
                        'batch' => $transfer->batch_number,
                        'reference' => $transfer->transfer_number,
                        'notes' => 'From '.self::levelLabel($transfer->from_level),
                    ];
                });
        }

        if ($level === 'modilon_hospital') {
            return StockTransfer::query()
                ->with(['drug', 'destinationDrug'])
                ->where('to_level', 'modilon_hospital')
                ->where('status', 'received')
                ->whereNotNull('received_at')
                ->whereBetween('received_at', [$from, $to])
                ->get()
                ->map(function (StockTransfer $transfer) {
                    $drug = $transfer->destinationDrug ?? $transfer->drug;

                    return [
                        'occurred_at' => $transfer->received_at,
                        'type' => 'transfer_in',
                        'type_label' => 'Received from Lae AMS',
                        'direction' => 'in',
                        'quantity' => (int) $transfer->quantity_sent,
                        'medicine' => $drug ? trim($drug->drug_name.' ('.$drug->dosage.')') : 'Unknown',
                        'batch' => $transfer->batch_number,
                        'reference' => $transfer->transfer_number,
                        'notes' => 'From '.self::levelLabel($transfer->from_level),
                    ];
                });
        }

        return collect();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected static function dispensing(string $level, Carbon $from, Carbon $to): Collection
    {
        if ($level !== 'modilon_hospital') {
            return collect();
        }

        return DispensingRecord::query()
            ->with('drug')
            ->whereBetween('dispensed_at', [$from, $to])
            ->get()
            ->map(function (DispensingRecord $record) {
                $drug = $record->drug;

                return [
                    'occurred_at' => $record->dispensed_at,
                    'type' => 'dispensed',
                    'type_label' => 'Dispensed to patient',
                    'direction' => 'out',
                    'quantity' => (int) $record->quantity_dispensed,
                    'medicine' => $drug ? trim($drug->drug_name.' ('.$drug->dosage.')') : 'Unknown',
                    'batch' => $drug?->batch_number,
                    'reference' => $record->record_number,
                    'notes' => $record->prescription_ref,
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected static function adjustments(string $level, Carbon $from, Carbon $to): Collection
    {
        return StockAdjustment::query()
            ->with('drug')
            ->atLevel($level)
            ->whereBetween('adjusted_at', [$from, $to])
            ->get()
            ->map(function (StockAdjustment $adjustment) {
                $drug = $adjustment->drug;
                $delta = (int) $adjustment->quantity_delta;

                return [
                    'occurred_at' => $adjustment->adjusted_at,
                    'type' => 'adjustment',
                    'type_label' => $adjustment->reasonLabel(),
                    'direction' => $delta >= 0 ? 'in' : 'out',
                    'quantity' => abs($delta),
                    'medicine' => $drug ? trim($drug->drug_name.' ('.$drug->dosage.')') : 'Unknown',
                    'batch' => $drug?->batch_number,
                    'reference' => $adjustment->adjustment_number,
                    'notes' => 'System '.$adjustment->quantity_system.' → counted '.$adjustment->quantity_counted,
                ];
            });
    }

    protected static function levelLabel(string $level): string
    {
        return match ($level) {
            'ndoh' => 'NDoH',
            'lae_ams' => 'Lae AMS',
            'modilon_hospital' => 'Modilon Hospital',
            default => $level,
        };
    }
}
