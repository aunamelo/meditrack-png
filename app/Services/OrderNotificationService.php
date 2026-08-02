<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderPendingApprovalNotification;
use App\Notifications\ServiceUpdateNotification;
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

    public static function notifyCreatorOfApproval(Order $order): void
    {
        $creator = $order->creator;

        if (! $creator) {
            return;
        }

        $creator->notify(new ServiceUpdateNotification(
            message: "Order {$order->order_number} was approved and is now in manufacturing.",
            entity: 'order',
            entityId: $order->id,
            reference: $order->order_number,
        ));
    }

    public static function notifyCreatorOfPipelineAdvance(Order $order): void
    {
        $creator = $order->creator;

        if (! $creator) {
            return;
        }

        $creator->notify(new ServiceUpdateNotification(
            message: "Order {$order->order_number} moved to {$order->statusLabel()}.",
            entity: 'order',
            entityId: $order->id,
            reference: $order->order_number,
        ));
    }

    public static function notifyCreatorOfReceipt(Order $order): void
    {
        $creator = $order->creator;

        if (! $creator) {
            return;
        }

        $creator->notify(new ServiceUpdateNotification(
            message: "Order {$order->order_number} was received into NDoH inventory.",
            entity: 'order',
            entityId: $order->id,
            reference: $order->order_number,
        ));
    }

    public static function notifyCreatorOfCancellation(Order $order): void
    {
        $creator = $order->creator;

        if (! $creator || $creator->id === auth()->id()) {
            return;
        }

        $creator->notify(new ServiceUpdateNotification(
            message: "Order {$order->order_number} was cancelled.",
            entity: 'order',
            entityId: $order->id,
            reference: $order->order_number,
        ));
    }

    /**
     * Mark order approval notifications as read for the given admin.
     */
    public static function markOrderNotificationsAsRead(User $admin, Order $order): void
    {
        $admin->unreadNotifications()
            ->whereIn('type', [
                OrderPendingApprovalNotification::class,
                ServiceUpdateNotification::class,
            ])
            ->get()
            ->each(function ($notification) use ($order) {
                $data = $notification->data;
                $matches = ((int) ($data['order_id'] ?? 0) === $order->id)
                    || (($data['entity'] ?? null) === 'order' && (int) ($data['entity_id'] ?? 0) === $order->id);

                if ($matches) {
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
            ->with(['items.drug', 'drug', 'creator'])
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
