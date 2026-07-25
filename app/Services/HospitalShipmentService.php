<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

class HospitalShipmentService
{
    /**
     * Dispatch an approved hospital order from Lae AMS to Modilon Hospital by road.
     * Lae AMS stock is deducted immediately; Modilon inventory is created on receipt only.
     */
    public static function ship(HospitalOrder $order, int $storeManagerId, ?string $notes = null): StockTransfer
    {
        if (! $order->canShip()) {
            throw new \InvalidArgumentException('This hospital order cannot be dispatched by road.');
        }

        $sourceDrug = Drug::findOrFail($order->source_drug_id);
        $quantitySent = (int) $order->quantity_approved;

        return DB::transaction(function () use ($order, $sourceDrug, $quantitySent, $storeManagerId, $notes) {
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
     */
    public static function confirmHospitalReceipt(HospitalOrder $order, int $userId, ?string $notes = null): void
    {
        if (! $order->canReceive() || ! $order->stockTransfer) {
            throw new \InvalidArgumentException('This hospital order cannot be received.');
        }

        DB::transaction(function () use ($order, $userId, $notes) {
            $transfer = $order->stockTransfer()->lockForUpdate()->first();

            if (! $transfer || ! $transfer->canReceive()) {
                throw new \InvalidArgumentException('This road delivery cannot be received.');
            }

            if (! $transfer->destination_drug_id) {
                $destinationDrug = self::createModilonInventoryFromTransfer($transfer, $userId);
                $transfer->update(['destination_drug_id' => $destinationDrug->id]);
            }

            $transfer->receive($userId, $notes);
            $order->update(['status' => 'received']);
        });
    }

    /**
     * Create Modilon Hospital inventory batch when pharmacy confirms receipt.
     */
    public static function createModilonInventoryFromTransfer(StockTransfer $transfer, int $userId): Drug
    {
        $sourceDrug = $transfer->drug;

        if (! $sourceDrug) {
            throw new \InvalidArgumentException('Source drug not found for this transfer.');
        }

        $quantitySent = (int) $transfer->quantity_sent;
        $destinationBatch = $sourceDrug->batch_number.'-MOD-'.now()->format('ymdHis');

        return Drug::create([
            'medicine_id' => $sourceDrug->medicine_id,
            'drug_name' => $sourceDrug->drug_name,
            'description' => $sourceDrug->description,
            'dosage' => $sourceDrug->dosage,
            'dosage_form' => $sourceDrug->dosage_form,
            'batch_number' => $destinationBatch,
            'expiry_date' => $sourceDrug->expiry_date,
            'quantity_received' => $quantitySent,
            'quantity_on_hand' => $quantitySent,
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
