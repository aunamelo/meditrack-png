<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\DiscrepancyReport;
use Illuminate\Support\Facades\DB;

class HospitalShipmentService
{
    /**
     * Dispatch an approved multi-item hospital order as ONE road delivery.
     * Each line becomes a stock_transfer_items row on the same transfer/vehicle.
     */
    public static function ship(
        HospitalOrder $order,
        int $storeManagerId,
        int $vehicleId,
        ?string $notes = null,
        ?\Carbon\CarbonInterface $expectedArrivalAt = null,
    ): StockTransfer {
        $order->loadMissing('items.sourceDrug');

        if (! $order->canShip()) {
            throw new \InvalidArgumentException('This hospital order cannot be dispatched by road.');
        }

        $items = $order->items;
        if ($items->isEmpty()) {
            throw new \InvalidArgumentException('This hospital order has no medicine lines to ship.');
        }

        $expectedArrivalAt ??= now()->addDays(2)->setTime(17, 0);

        return DB::transaction(function () use ($order, $items, $storeManagerId, $vehicleId, $notes, $expectedArrivalAt) {
            $transferNumber = StockTransfer::generateTransferNumber();
            $first = $items->first();
            $firstSource = Drug::query()->lockForUpdate()->findOrFail($first->source_drug_id);
            $totalSent = (int) $items->sum('quantity_approved');

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                // Header keeps first line for older screens / live-map label.
                'drug_id' => $firstSource->id,
                'destination_drug_id' => null,
                'hospital_order_id' => $order->id,
                'vehicle_id' => $vehicleId,
                'batch_number' => $firstSource->batch_number,
                'quantity_sent' => $totalSent,
                'from_level' => 'lae_ams',
                'to_level' => 'modilon_hospital',
                'sent_date' => now()->toDateString(),
                'expected_arrival_at' => $expectedArrivalAt,
                'status' => 'sent',
                'notes' => $notes,
                'sent_by' => $storeManagerId,
            ]);

            foreach ($items as $item) {
                $sourceDrug = Drug::query()->lockForUpdate()->findOrFail($item->source_drug_id);
                $quantitySent = (int) $item->quantity_approved;

                if ($sourceDrug->quantity_on_hand < $quantitySent) {
                    throw new \InvalidArgumentException(
                        "Insufficient Lae AMS stock for {$item->displayLabel()}. Available: {$sourceDrug->quantity_on_hand}."
                    );
                }

                $sourceDrug->update([
                    'quantity_on_hand' => $sourceDrug->quantity_on_hand - $quantitySent,
                    'last_issued_date' => now(),
                    'updated_by' => $storeManagerId,
                ]);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'hospital_order_item_id' => $item->id,
                    'drug_id' => $sourceDrug->id,
                    'destination_drug_id' => null,
                    'batch_number' => $sourceDrug->batch_number,
                    'quantity_sent' => $quantitySent,
                ]);
            }

            $order->update([
                'status' => 'shipped',
                'stock_transfer_id' => $transfer->id,
            ]);

            return $transfer->load('items.drug');
        });
    }

    /**
     * Confirm hospital receipt for all lines on one delivery.
     *
     * @param  array{
     *     items: array<int, array{id: int, quantity_received: int}>,
     *     batch_verified: bool,
     *     expiry_verified: bool,
     *     condition: string,
     *     notes?: string|null
     * }  $verification
     */
    public static function confirmHospitalReceipt(HospitalOrder $order, int $userId, array $verification): void
    {
        if (! $order->canReceive() || ! $order->stockTransfer) {
            throw new \InvalidArgumentException('This hospital order cannot be received.');
        }

        DB::transaction(function () use ($order, $userId, $verification) {
            $transfer = $order->stockTransfer()->with('items.drug')->lockForUpdate()->first();

            if (! $transfer || ! $transfer->canReceive()) {
                throw new \InvalidArgumentException('This road delivery cannot be received.');
            }

            $receivedByItemId = collect($verification['items'] ?? [])->keyBy('id');
            $condition = $verification['condition'];
            $notes = $verification['notes'] ?? null;
            $totalSent = 0;
            $totalReceived = 0;
            $firstDestinationId = null;

            $lines = $transfer->items->isNotEmpty()
                ? $transfer->items
                : collect([null]); // legacy single-drug transfer without item rows

            if ($transfer->items->isEmpty()) {
                $quantitySent = (int) $transfer->quantity_sent;
                $quantityReceived = (int) ($verification['quantity_received'] ?? $quantitySent);
                $totalSent = $quantitySent;
                $totalReceived = $quantityReceived;

                if ($quantityReceived > 0 && ! $transfer->destination_drug_id) {
                    $destinationDrug = self::createModilonInventoryFromSource(
                        $transfer->drug,
                        $transfer,
                        $userId,
                        $quantityReceived
                    );
                    $transfer->update(['destination_drug_id' => $destinationDrug->id]);
                    $firstDestinationId = $destinationDrug->id;
                }
            } else {
                foreach ($transfer->items as $line) {
                    $row = $receivedByItemId->get($line->id);
                    $quantitySent = (int) $line->quantity_sent;
                    $quantityReceived = (int) ($row['quantity_received'] ?? $quantitySent);
                    $totalSent += $quantitySent;
                    $totalReceived += $quantityReceived;

                    $destinationId = $line->destination_drug_id;
                    if ($quantityReceived > 0 && ! $destinationId) {
                        $destinationDrug = self::createModilonInventoryFromSource(
                            $line->drug,
                            $transfer,
                            $userId,
                            $quantityReceived
                        );
                        $destinationId = $destinationDrug->id;
                        if (! $firstDestinationId) {
                            $firstDestinationId = $destinationId;
                        }
                    }

                    $line->update([
                        'destination_drug_id' => $destinationId,
                        'quantity_received' => $quantityReceived,
                    ]);
                }
            }

            $receiptNote = trim(implode(' ', array_filter([
                $notes,
                "Verified count: {$totalReceived}/{$totalSent} across delivery.",
                'Batch checked: yes.',
                'Expiry checked: yes.',
                'Condition: '.$condition.'.',
            ])));

            if ($firstDestinationId && ! $transfer->destination_drug_id) {
                $transfer->update(['destination_drug_id' => $firstDestinationId]);
            }

            // Mark transfer received without creating Lae inventory (hospital path).
            $transfer->update([
                'status' => 'received',
                'received_by' => $userId,
                'received_at' => now(),
                'notes' => trim(($transfer->notes ? $transfer->notes."\n\n" : '').'Receipt note: '.$receiptNote),
            ]);

            $order->update(['status' => 'received']);

            $needsDiscrepancy = $totalReceived < $totalSent || $condition !== 'good';
            if ($needsDiscrepancy) {
                $issueType = $condition === 'good' ? 'short_shipment' : $condition;

                DiscrepancyReport::create([
                    'report_number' => DiscrepancyReport::generateReportNumber(),
                    'hospital_order_id' => $order->id,
                    'stock_transfer_id' => $transfer->id,
                    'issue_type' => $issueType,
                    'quantity_expected' => $totalSent,
                    'quantity_received' => $totalReceived,
                    'description' => $receiptNote !== ''
                        ? $receiptNote
                        : "Receipt verification reported {$issueType} for order {$order->order_number}.",
                    'reported_by' => $userId,
                    'status' => 'open',
                ]);
            }
        });
    }

    public static function createModilonInventoryFromSource(
        Drug $sourceDrug,
        StockTransfer $transfer,
        int $userId,
        int $quantityReceived,
    ): Drug {
        $destinationBatch = $sourceDrug->batch_number.'-MOD-'.now()->format('ymdHis').'-'.$sourceDrug->id;

        return Drug::create([
            'medicine_id' => $sourceDrug->medicine_id,
            'drug_name' => $sourceDrug->drug_name,
            'description' => $sourceDrug->description,
            'dosage' => $sourceDrug->dosage,
            'dosage_form' => $sourceDrug->dosage_form,
            'batch_number' => $destinationBatch,
            'expiry_date' => $sourceDrug->expiry_date,
            'quantity_received' => $quantityReceived,
            'quantity_on_hand' => $quantityReceived,
            'reorder_point' => $sourceDrug->reorder_point,
            'unit' => $sourceDrug->unit,
            'supplier' => $sourceDrug->supplier,
            'cost_per_unit' => $sourceDrug->cost_per_unit,
            'storage_location' => 'Modilon Hospital Pharmacy',
            'level' => 'modilon_hospital',
            'status' => 'active',
            'received_date' => now(),
            'notes' => "Received via road delivery {$transfer->transfer_number} from Lae AMS",
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    /**
     * @deprecated Use createModilonInventoryFromSource
     */
    public static function createModilonInventoryFromTransfer(StockTransfer $transfer, int $userId, ?int $quantityReceived = null): Drug
    {
        $sourceDrug = $transfer->drug;
        if (! $sourceDrug) {
            throw new \InvalidArgumentException('Source drug not found for this transfer.');
        }

        return self::createModilonInventoryFromSource(
            $sourceDrug,
            $transfer,
            $userId,
            $quantityReceived ?? (int) $transfer->quantity_sent
        );
    }
}
