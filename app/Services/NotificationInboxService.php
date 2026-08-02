<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class NotificationInboxService
{
    /**
     * @return Collection<int, DatabaseNotification>
     */
    public static function recentFor(User $user, int $limit = 12): Collection
    {
        return $user->notifications()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public static function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public static function actionUrl(DatabaseNotification $notification): ?string
    {
        $data = $notification->data;
        $entity = $data['entity'] ?? null;
        $entityId = (int) ($data['entity_id'] ?? $data['order_id'] ?? $data['transfer_id'] ?? $data['hospital_order_id'] ?? $data['discrepancy_id'] ?? 0);

        if ($entityId < 1) {
            return null;
        }

        try {
            return match ($entity) {
                'order' => getDashboardOrderRoute('show', $entityId),
                'transfer' => getDashboardTransferRoute('show', $entityId),
                'hospital_order' => getDashboardHospitalOrderRoute('show', $entityId),
                'discrepancy' => getDashboardDiscrepancyRoute('show', $entityId),
                default => self::legacyUrl($data),
            };
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function legacyUrl(array $data): ?string
    {
        if (! empty($data['order_id'])) {
            return getDashboardOrderRoute('show', (int) $data['order_id']);
        }

        if (! empty($data['transfer_id'])) {
            return getDashboardTransferRoute('show', (int) $data['transfer_id']);
        }

        return null;
    }

    public static function markAsRead(User $user, DatabaseNotification $notification): void
    {
        if ($notification->notifiable_id === $user->id && $notification->notifiable_type === $user::class) {
            $notification->markAsRead();
        }
    }

    public static function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
