<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderPendingApprovalNotification;
use Illuminate\Support\Collection;

class OrderNotificationService
{
    /**
     * Notify all NDoH admins that a procurement order needs approval.
     */
    public static function notifyAdminsOfPendingOrder(Order $order): void
    {
        self::adminUsers()->each(
            fn (User $admin) => $admin->notify(new OrderPendingApprovalNotification($order))
        );
    }

    /**
     * Mark order approval notifications as read for the given admin.
     */
    public static function markOrderNotificationsAsRead(User $admin, Order $order): void
    {
        $admin->unreadNotifications()
            ->where('type', OrderPendingApprovalNotification::class)
            ->get()
            ->each(function ($notification) use ($order) {
                if ((int) ($notification->data['order_id'] ?? 0) === $order->id) {
                    $notification->markAsRead();
                }
            });
    }

    /**
     * @return Collection<int, Order>
     */
    public static function pendingOrdersForAdmin(int $limit = 5): Collection
    {
        return Order::query()
            ->pending()
            ->with(['drug', 'creator'])
            ->orderByDesc('created_at')
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
}
