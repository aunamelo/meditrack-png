<?php

namespace App\Services;

use App\Models\DiscrepancyReport;
use App\Models\HospitalOrder;
use App\Models\User;
use App\Notifications\ServiceUpdateNotification;
use Illuminate\Support\Collection;

class HospitalOrderNotificationService
{
    public static function notifyStoreManagersOfRequest(HospitalOrder $order): void
    {
        self::storeManagers()->each(
            fn (User $manager) => $manager->notify(new ServiceUpdateNotification(
                message: "Hospital order {$order->order_number} ({$order->medicinesLabel()}) needs Lae AMS review.",
                entity: 'hospital_order',
                entityId: $order->id,
                reference: $order->order_number,
            ))
        );
    }

    public static function notifyRequesterOfDecision(HospitalOrder $order): void
    {
        $requester = $order->requester;

        if (! $requester) {
            return;
        }

        $message = match ($order->status) {
            'approved' => "Hospital order {$order->order_number} was approved at Lae AMS.",
            'rejected' => "Hospital order {$order->order_number} was rejected".($order->rejection_reason ? ': '.$order->rejection_reason : '.'),
            default => "Hospital order {$order->order_number} was updated.",
        };

        $requester->notify(new ServiceUpdateNotification(
            message: $message,
            entity: 'hospital_order',
            entityId: $order->id,
            reference: $order->order_number,
        ));
    }

    public static function notifyRequesterOfShipment(HospitalOrder $order): void
    {
        $requester = $order->requester;

        if (! $requester) {
            return;
        }

        $transferNumber = $order->stockTransfer?->transfer_number;

        $requester->notify(new ServiceUpdateNotification(
            message: $transferNumber
                ? "Hospital order {$order->order_number} has been dispatched ({$transferNumber}). Track receipt at Modilon."
                : "Hospital order {$order->order_number} has been dispatched by road to Modilon.",
            entity: 'hospital_order',
            entityId: $order->id,
            reference: $order->order_number,
        ));
    }

    public static function notifyStoreManagersOfReceipt(HospitalOrder $order): void
    {
        self::storeManagers()->each(
            fn (User $manager) => $manager->notify(new ServiceUpdateNotification(
                message: "Hospital order {$order->order_number} was confirmed received at Modilon Hospital.",
                entity: 'hospital_order',
                entityId: $order->id,
                reference: $order->order_number,
            ))
        );
    }

    public static function notifyStoreManagersOfDiscrepancy(DiscrepancyReport $report): void
    {
        $report->loadMissing('hospitalOrder');
        $orderNumber = $report->hospitalOrder?->order_number ?? 'a hospital delivery';

        self::storeManagers()->each(
            fn (User $manager) => $manager->notify(new ServiceUpdateNotification(
                message: "Discrepancy {$report->report_number} filed against {$orderNumber}.",
                entity: 'discrepancy',
                entityId: $report->id,
                reference: $report->report_number,
            ))
        );
    }

    /**
     * @return Collection<int, User>
     */
    private static function storeManagers(): Collection
    {
        return User::role('store_manager')->get();
    }
}
