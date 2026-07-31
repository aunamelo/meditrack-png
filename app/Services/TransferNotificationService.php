<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\User;
use App\Notifications\ShipmentIncomingNotification;
use App\Notifications\ShipmentPendingApprovalNotification;
use Illuminate\Support\Collection;

class TransferNotificationService
{
    /**
     * Notify all NDoH admins that a shipment needs approval before send.
     */
    public static function notifyAdminsOfPendingShipment(StockTransfer $transfer): void
    {
        self::adminUsers()->each(
            fn (User $admin) => $admin->notify(new ShipmentPendingApprovalNotification($transfer))
        );
    }

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
     * Mark shipment notifications as read for the given user.
     */
    public static function markTransferNotificationsAsRead(User $user, StockTransfer $transfer): void
    {
        $user->unreadNotifications()
            ->whereIn('type', [
                ShipmentIncomingNotification::class,
                ShipmentPendingApprovalNotification::class,
            ])
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
    public static function pendingShipmentsForAdmin(int $limit = 5): Collection
    {
        return StockTransfer::query()
            ->pending()
            ->fromLevel('ndoh')
            ->toLevel('lae_ams')
            ->with(['drug', 'sender'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, StockTransfer>
     */
    public static function pendingShipmentsForStoreManager(int $limit = 5): Collection
    {
        return StockTransfer::query()
            ->sent()
            ->toLevel('lae_ams')
            ->whereNull('hospital_order_id')
            ->with(['drug', 'sender'])
            ->orderByDesc('sent_date')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    private static function adminUsers(): Collection
    {
        return User::role('admin')->get();
    }

    /**
     * @return Collection<int, User>
     */
    private static function storeManagerUsers(): Collection
    {
        return User::role('store_manager')->get();
    }
}
