<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Generic in-app alert for e-service lifecycle events (DICT 2.6).
 */
class ServiceUpdateNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $message,
        public string $entity,
        public int $entityId,
        public ?string $reference = null,
        public array $meta = [],
    ) {}

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
        $payload = array_merge($this->meta, [
            'message' => $this->message,
            'entity' => $this->entity,
            'entity_id' => $this->entityId,
            'reference' => $this->reference,
        ]);

        // Back-compat keys used by existing mark-as-read helpers.
        if ($this->entity === 'order') {
            $payload['order_id'] = $this->entityId;
            $payload['order_number'] = $this->reference;
        }

        if ($this->entity === 'transfer') {
            $payload['transfer_id'] = $this->entityId;
            $payload['transfer_number'] = $this->reference;
        }

        if ($this->entity === 'hospital_order') {
            $payload['hospital_order_id'] = $this->entityId;
        }

        if ($this->entity === 'discrepancy') {
            $payload['discrepancy_id'] = $this->entityId;
        }

        return $payload;
    }
}
