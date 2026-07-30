<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\StockTransfer;
use App\Models\DiscrepancyReport;
use Illuminate\Support\Facades\DB;

class HospitalShipmentService
{
    /**
     * Dispatch an approved hospital order from Lae AMS to Modilon Hospital by road.
     * Lae AMS stock is deducted immediately; Modilon inventory is created on receipt only.
     */
    public static function ship(HospitalOrder $order, int $storeManagerId, int $vehicleId, ?string $notes = null): StockTransfer
    {
        if (! $order->canShip()) {
            throw new \InvalidArgumentException('This hospital order cannot be dispatched by road.');
        }

        $sourceDrug = Drug::findOrFail($order->source_drug_id);
        $quantitySent = (int) $order->quantity_approved;

        return DB::transaction(function () use ($order, $sourceDrug, $quantitySent, $storeManagerId, $vehicleId, $notes) {
            $transferNumber = StockTransfer::generateTransferNumber();

            $sourceDrug->update([
                'quantity_on_hand' => $sourceDrug->quantity_on_hand - $quantitySent,
                'last_issued_date' => now(),
                'updated_by' => $storeManagerId,
            ]);

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'drug_id' => $sourceDrug->id,
                'destination_drug_id' => null,
                'hospital_order_id' => $order->id,
                'vehicle_id' => $vehicleId,
                'batch_number' => $sourceDrug->batch_number,
                'quantity_sent' => $quantitySent,
                'from_level' => 'lae_ams',
                'to_level' => 'modilon_hospital',
                'sent_date' => now()->toDateString(),
                'status' => 'sent',
                'notes' => $notes,
                'sent_by' => $storeManagerId,
            ]);

            $order->update([
                'status' => 'shipped',
                'stock_transfer_id' => $transfer->id,
            ]);

            return $transfer;
        });
    }

    /**
     * Confirm hospital receipt at Modilon (Pharmacy Manager) and create hospital inventory.
     *
     * @param  array{
     *     quantity_received: int,
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
            $transfer = $order->stockTransfer()->lockForUpdate()->first();

            if (! $transfer || ! $transfer->canReceive()) {
                throw new \InvalidArgumentException('This road delivery cannot be received.');
            }

            $quantitySent = (int) $transfer->quantity_sent;
            $quantityReceived = (int) $verification['quantity_received'];
            $condition = $verification['condition'];
            $notes = $verification['notes'] ?? null;

            $receiptNote = trim(implode(' ', array_filter([
                $notes,
                "Verified count: {$quantityReceived}/{$quantitySent}.",
                'Batch checked: yes.',
                'Expiry checked: yes.',
                'Condition: '.$condition.'.',
            ])));

            if ($quantityReceived > 0 && ! $transfer->destination_drug_id) {
                $destinationDrug = self::createModilonInventoryFromTransfer($transfer, $userId, $quantityReceived);
                $transfer->update(['destination_drug_id' => $destinationDrug->id]);
            }

            $transfer->receive($userId, $receiptNote);
            $order->update(['status' => 'received']);

            $needsDiscrepancy = $quantityReceived < $quantitySent || $condition !== 'good';
            if ($needsDiscrepancy) {
                $issueType = $condition === 'good' ? 'short_shipment' : $condition;

                DiscrepancyReport::create([
                    'report_number' => DiscrepancyReport::generateReportNumber(),
                    'hospital_order_id' => $order->id,
                    'stock_transfer_id' => $transfer->id,
                    'issue_type' => $issueType,
                    'quantity_expected' => $quantitySent,
                    'quantity_received' => $quantityReceived,
                    'description' => $receiptNote !== ''
                        ? $receiptNote
                        : "Receipt verification reported {$issueType} for order {$order->order_number}.",
                    'reported_by' => $userId,
                    'status' => 'open',
                ]);
            }
        });
    }

    /**
     * Create Modilon Hospital inventory batch when pharmacy confirms receipt.
     */
    public static function createModilonInventoryFromTransfer(StockTransfer $transfer, int $userId, ?int $quantityReceived = null): Drug
    {
        $sourceDrug = $transfer->drug;

        if (! $sourceDrug) {
            throw new \InvalidArgumentException('Source drug not found for this transfer.');
        }

        $quantity = $quantityReceived ?? (int) $transfer->quantity_sent;
        $destinationBatch = $sourceDrug->batch_number.'-MOD-'.now()->format('ymdHis');

        return Drug::create([
            'medicine_id' => $sourceDrug->medicine_id,
            'drug_name' => $sourceDrug->drug_name,
            'description' => $sourceDrug->description,
            'dosage' => $sourceDrug->dosage,
            'dosage_form' => $sourceDrug->dosage_form,
            'batch_number' => $destinationBatch,
            'expiry_date' => $sourceDrug->expiry_date,
            'quantity_received' => $quantity,
            'quantity_on_hand' => $quantity,
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
}
