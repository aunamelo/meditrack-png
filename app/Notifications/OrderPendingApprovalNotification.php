<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPendingApprovalNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

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
        $this->order->loadMissing(['drug', 'creator']);

        $drugLabel = $this->order->drug
            ? "{$this->order->drug->drug_name} ({$this->order->drug->dosage})"
            : 'Unknown drug';

        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'drug_name' => $drugLabel,
            'quantity_ordered' => $this->order->quantity_ordered,
            'supplier' => $this->order->supplier,
            'created_by' => $this->order->creator->name ?? 'Procurement Officer',
            'message' => "Order {$this->order->order_number} for {$drugLabel} requires NDoH approval.",
        ];
    }
}
