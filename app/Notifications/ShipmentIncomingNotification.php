<?php

namespace App\Notifications;

use App\Models\StockTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShipmentIncomingNotification extends Notification
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

        $drugName = $this->transfer->drug->drug_name ?? 'Unknown drug';

        return [
            'transfer_id' => $this->transfer->id,
            'transfer_number' => $this->transfer->transfer_number,
            'drug_name' => $drugName,
            'batch_number' => $this->transfer->batch_number,
            'quantity_sent' => $this->transfer->quantity_sent,
            'sent_by' => $this->transfer->sender->name ?? 'Procurement Officer',
            'message' => "Road delivery {$this->transfer->transfer_number} ({$drugName}) is en route to Lae AMS by car.",
        ];
    }
}
