<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\User;
use App\Notifications\ShipmentIncomingNotification;
use Illuminate\Support\Collection;

class TransferNotificationService
{
    /**
     * Notify all Lae AMS store managers of an incoming shipment.
     */
    public static function notifyStoreManagersOfShipment(StockTransfer $transfer): void
    {
        self::storeManagerUsers()->each(
            fn (User $manager) => $manager->notify(new ShipmentIncomingNotification($transfer))
        );
    }

    /**
     * Mark shipment notifications as read for the given store manager.
     */
    public static function markTransferNotificationsAsRead(User $manager, StockTransfer $transfer): void
    {
        $manager->unreadNotifications()
            ->where('type', ShipmentIncomingNotification::class)
            ->get()
            ->each(function ($notification) use ($transfer) {
                if ((int) ($notification->data['transfer_id'] ?? 0) === $transfer->id) {
                    $notification->markAsRead();
                }
            });
    }

    /**
     * @return Collection<int, StockTransfer>
     */
    public static function pendingShipmentsForStoreManager(int $limit = 5): Collection
    {
        return StockTransfer::query()
            ->sent()
            ->toLevel('lae_ams')
            ->with(['drug', 'sender'])
            ->orderByDesc('sent_date')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    private static function storeManagerUsers(): Collection
    {
        return User::role('store_manager')->get();
    }
}
