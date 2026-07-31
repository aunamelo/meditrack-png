<?php

namespace App\Notifications;

use App\Models\StockTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShipmentPendingApprovalNotification extends Notification
{
    use Queueable;

    public function __construct(public StockTransfer $transfer) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->transfer->loadMissing(['drug', 'sender']);

        $drugLabel = $this->transfer->drug
            ? $this->transfer->drug->drug_name.' ('.$this->transfer->drug->dosage.')'
            : 'Unknown drug';

        return [
            'transfer_id' => $this->transfer->id,
            'transfer_number' => $this->transfer->transfer_number,
            'drug_name' => $drugLabel,
            'quantity_sent' => $this->transfer->quantity_sent,
            'created_by' => $this->transfer->sender->name ?? 'Procurement Officer',
            'message' => "Shipment {$this->transfer->transfer_number} for {$drugLabel} requires NDoH approval before it can be sent to Lae AMS.",
        ];
    }
}
