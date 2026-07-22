<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\Order;
use App\Models\StockTransfer;
use App\Models\User;
use App\Services\OrderNotificationService;
use App\Services\TransferNotificationService;

class DashboardService
{
    /**
     * Build dashboard payload for the authenticated user.
     *
     * @return array<string, mixed>
     */
    public static function forUser(?User $user): array
    {
        if (! $user) {
            return self::guestPayload();
        }

        $meta = PortalNavigationService::currentRoleMeta();

        if (! $meta) {
            return self::guestPayload();
        }

        return match ($meta['key']) {
            'admin' => self::adminPayload($user, $meta),
            'procurement_officer' => self::procurementPayload($user, $meta),
            'store_manager' => self::storeManagerPayload($user, $meta),
            'pharmacy_manager' => self::pharmacyManagerPayload($user, $meta),
            'pharmacist' => self::pharmacistPayload($user, $meta),
            default => self::guestPayload(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected static function guestPayload(): array
    {
        return [
            'roleMeta' => null,
            'stats' => [],
            'alerts' => [],
            'quickActions' => [],
            'recentItems' => [],
            'supplyChainHighlight' => null,
            'charts' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected static function adminPayload(User $user, array $meta): array
    {
        $level = $meta['inventory_level'];
        $pendingCount = Order::pending()->count();

        return [
            'roleMeta' => $meta,
            'stats' => [
                self::stat('Pending approvals', (string) $pendingCount, 'Awaiting sign-off', 'amber', getDashboardOrderRoute('index').'?status=pending', 'bell'),
                self::stat('Stock batches', (string) Drug::atLevel($level)->inInventory()->count(), 'NDoH inventory', 'teal', getDashboardDrugRoute('index'), 'cube'),
                self::stat('In transit', (string) StockTransfer::sent()->toLevel('lae_ams')->count(), 'Shipments to Lae AMS', 'blue', getDashboardTransferRoute('index').'?status=sent', 'truck'),
            ],
            'alerts' => $pendingCount > 0 ? [[
                'tone' => 'amber',
                'title' => 'Pending procurement orders',
                'message' => $pendingCount === 1
                    ? '1 order needs your approval before the supplier can be confirmed.'
                    : "{$pendingCount} orders need your approval before suppliers can be confirmed.",
                'action_label' => 'Review orders',
                'action_url' => getDashboardOrderRoute('index').'?status=pending',
                'badge' => $user->unreadNotifications()->count() ?: null,
                'items' => OrderNotificationService::pendingOrdersForAdmin()->map(fn (Order $order) => [
                    'title' => $order->order_number,
                    'subtitle' => $order->itemsSummary().' · '.number_format($order->quantity_ordered).' units · '.$order->supplier,
                    'url' => getDashboardOrderRoute('show', $order),
                    'action' => 'Approve',
                ])->all(),
            ]] : [],
            'quickActions' => self::quickActions(array_values(array_filter([
                ['label' => 'Review orders', 'description' => 'Approve pending procurement', 'url' => getDashboardOrderRoute('index'), 'primary' => true, 'icon' => 'clipboard'],
                ['label' => 'Drug inventory', 'description' => 'NDoH central stock', 'url' => getDashboardDrugRoute('index'), 'icon' => 'cube'],
                ['label' => 'Track shipments', 'description' => 'NDoH → Lae AMS logistics', 'url' => getDashboardTransferRoute('index'), 'icon' => 'truck'],
                canManageUsers() ? ['label' => 'User management', 'description' => 'Manage portal accounts', 'url' => getDashboardUserRoute('index'), 'icon' => 'shield'] : null,
            ]))),
            'recentItems' => Order::with(['items.drug', 'drug'])->latest()->limit(4)->get()->map(fn (Order $order) => self::recentOrderRow($order))->all(),
            'supplyChainHighlight' => 'ndoh',
            'charts' => DashboardChartService::forRole('admin'),
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected static function procurementPayload(User $user, array $meta): array
    {
        $level = $meta['inventory_level'];
        $myPending = Order::pending()->where('created_by', $user->id)->count();
        $myOrders = Order::where('created_by', $user->id)->count();

        return [
            'roleMeta' => $meta,
            'stats' => [
                self::stat('Pending', (string) $myPending, 'Awaiting approval', 'amber', getDashboardOrderRoute('index').'?status=pending', 'bell'),
                self::stat('My orders', (string) $myOrders, 'Total submitted', 'teal', getDashboardOrderRoute('index'), 'clipboard'),
                self::stat('In transit', (string) StockTransfer::sent()->fromLevel('ndoh')->count(), 'Sent to Lae AMS', 'blue', getDashboardTransferRoute('index'), 'truck'),
            ],
            'alerts' => [],
            'quickActions' => self::quickActions([
                ['label' => 'New order', 'description' => 'Create procurement order', 'url' => getDashboardOrderRoute('create'), 'primary' => true, 'icon' => 'plus'],
                ['label' => 'My orders', 'description' => 'Track approval status', 'url' => getDashboardOrderRoute('index'), 'icon' => 'clipboard'],
                ['label' => 'Ship to Lae AMS', 'description' => 'Dispatch NDoH stock to Lae AMS', 'url' => getDashboardTransferRoute('create'), 'icon' => 'truck'],
                ['label' => 'Drug catalog', 'description' => 'NDoH medicine types', 'url' => getDashboardDrugRoute('index'), 'icon' => 'cube'],
            ]),
            'recentItems' => Order::where('created_by', $user->id)->latest()->limit(4)->get()->map(fn (Order $order) => self::recentOrderRow($order))->all(),
            'supplyChainHighlight' => 'ndoh',
            'charts' => DashboardChartService::forRole('procurement_officer', $user->id),
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected static function storeManagerPayload(User $user, array $meta): array
    {
        $level = $meta['inventory_level'];
        $pendingShipments = StockTransfer::sent()->toLevel('lae_ams')->count();
        $pendingHospitalOrders = \App\Models\HospitalOrder::pending()->count();
        $lowStock = Drug::atLevel($level)->lowStock()->count();

        $alerts = [];

        if ($pendingHospitalOrders > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Hospital orders awaiting review',
                'message' => $pendingHospitalOrders === 1
                    ? '1 Modilon Hospital request needs your approval.'
                    : "{$pendingHospitalOrders} Modilon Hospital requests need your approval.",
                'action_label' => 'Review orders',
                'action_url' => getDashboardHospitalOrderRoute('index').'?status=pending',
            ];
        }

        if ($lowStock > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Low stock at Lae AMS',
                'message' => $lowStock === 1
                    ? '1 batch is below reorder point.'
                    : "{$lowStock} batches are below reorder point.",
                'action_label' => 'View inventory',
                'action_url' => getDashboardDrugRoute('index'),
            ];
        }

        if ($pendingShipments > 0) {
            $alerts[] = [
                'tone' => 'blue',
                'title' => 'Incoming shipments from NDoH',
                'message' => $pendingShipments === 1
                    ? '1 shipment is awaiting confirmation at Lae AMS.'
                    : "{$pendingShipments} shipments are awaiting confirmation at Lae AMS.",
                'action_label' => 'View shipments',
                'action_url' => getDashboardTransferRoute('index').'?status=sent',
                'badge' => $user->unreadNotifications()->count() ?: null,
                'items' => TransferNotificationService::pendingShipmentsForStoreManager()->map(fn (StockTransfer $transfer) => [
                    'title' => $transfer->transfer_number,
                    'subtitle' => ($transfer->drug->drug_name ?? 'Unknown drug').' · '.number_format($transfer->quantity_sent).' units · Batch '.$transfer->batch_number,
                    'url' => getDashboardTransferRoute('show', $transfer),
                    'action' => 'Confirm',
                ])->all(),
            ];
        }

        return [
            'roleMeta' => $meta,
            'stats' => [
                self::stat('Hospital orders', (string) $pendingHospitalOrders, 'Awaiting approval', 'amber', getDashboardHospitalOrderRoute('index').'?status=pending', 'hospital'),
                self::stat('Awaiting receipt', (string) $pendingShipments, 'NDoH → Lae AMS', 'blue', getDashboardTransferRoute('index').'?status=sent', 'truck'),
                self::stat('Stock batches', (string) Drug::atLevel($level)->inInventory()->count(), 'On hand at Lae AMS', 'teal', getDashboardDrugRoute('index'), 'cube'),
            ],
            'alerts' => $alerts,
            'quickActions' => self::quickActions([
                ['label' => 'Hospital orders', 'description' => 'Approve Modilon requests', 'url' => getDashboardHospitalOrderRoute('index'), 'primary' => true, 'icon' => 'hospital'],
                ['label' => 'Confirm NDoH receipts', 'description' => 'Receive national shipments', 'url' => getDashboardTransferRoute('index'), 'icon' => 'truck'],
                ['label' => 'Regional report', 'description' => 'Lae AMS summary', 'url' => getDashboardRegionalReportRoute('index'), 'icon' => 'chart'],
            ]),
            'recentItems' => \App\Models\HospitalOrder::latest()->limit(4)->get()->map(fn ($order) => [
                'title' => $order->order_number,
                'subtitle' => $order->drug_name.' · '.number_format($order->quantity_requested).' units requested',
                'meta' => hospitalOrderStatusLabel($order->status),
                'url' => getDashboardHospitalOrderRoute('show', $order),
            ])->all(),
            'supplyChainHighlight' => 'lae_ams',
            'charts' => DashboardChartService::forRole('store_manager', null, $level),
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected static function pharmacyManagerPayload(User $user, array $meta): array
    {
        $level = $meta['inventory_level'];
        $lowStock = Drug::atLevel($level)->lowStock()->count();
        $expiring = Drug::atLevel($level)->expiring()->count();
        $pendingRequests = \App\Models\HospitalOrder::where('requested_by', $user->id)->pending()->count();

        $alerts = [];

        if ($lowStock > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Low stock at Modilon Hospital',
                'message' => $lowStock === 1
                    ? '1 medicine batch needs replenishment.'
                    : "{$lowStock} medicine batches need replenishment.",
                'action_label' => 'View inventory',
                'action_url' => getDashboardDrugRoute('index'),
            ];
        }

        if ($expiring > 0) {
            $alerts[] = [
                'tone' => 'blue',
                'title' => 'Medicines expiring soon',
                'message' => $expiring === 1
                    ? '1 batch expires within 6 months.'
                    : "{$expiring} batches expire within 6 months.",
                'action_label' => 'Review stock',
                'action_url' => getDashboardDrugRoute('index'),
            ];
        }

        return [
            'roleMeta' => $meta,
            'stats' => [
                self::stat('Stock batches', (string) Drug::atLevel($level)->inInventory()->count(), 'Hospital inventory', 'teal', getDashboardDrugRoute('index'), 'cube'),
                self::stat('Low stock', (string) $lowStock, 'Needs replenishment', 'amber', getDashboardDrugRoute('index'), 'bell'),
                self::stat('Open requests', (string) $pendingRequests, 'Awaiting Lae AMS', 'blue', getDashboardHospitalOrderRoute('index').'?status=pending', 'clipboard'),
            ],
            'alerts' => $alerts,
            'quickActions' => self::quickActions(array_values(array_filter([
                ['label' => 'Request from Lae AMS', 'description' => 'Submit hospital order', 'url' => getDashboardHospitalOrderRoute('create'), 'primary' => true, 'icon' => 'plus'],
                ['label' => 'Hospital inventory', 'description' => 'Manage Modilon stock', 'url' => getDashboardDrugRoute('index'), 'icon' => 'cube'],
                ['label' => 'Incoming deliveries', 'description' => 'Track Lae AMS road deliveries', 'url' => getDashboardHospitalShipmentRoute('index'), 'icon' => 'truck'],
                canManageUsers() ? ['label' => 'Manage pharmacists', 'description' => 'Pharmacy team accounts', 'url' => getDashboardUserRoute('index'), 'icon' => 'shield'] : null,
            ]))),
            'recentItems' => \App\Models\HospitalOrder::where('requested_by', $user->id)->latest()->limit(4)->get()->map(fn ($order) => [
                'title' => $order->order_number,
                'subtitle' => $order->drug_name.' · '.number_format($order->quantity_requested).' units requested',
                'meta' => hospitalOrderStatusLabel($order->status),
                'url' => getDashboardHospitalOrderRoute('show', $order),
            ])->all(),
            'supplyChainHighlight' => 'modilon_hospital',
            'charts' => DashboardChartService::forRole('pharmacy_manager', null, $level),
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected static function pharmacistPayload(User $user, array $meta): array
    {
        $level = $meta['inventory_level'];
        $lowStock = Drug::atLevel($level)->lowStock()->count();
        $expiring = Drug::atLevel($level)->expiring()->count();

        $alerts = [];

        if ($lowStock > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Check low stock before dispensing',
                'message' => $lowStock === 1
                    ? '1 batch is below reorder point.'
                    : "{$lowStock} batches are below reorder point.",
                'action_label' => 'View inventory',
                'action_url' => getDashboardDrugRoute('index'),
            ];
        }

        return [
            'roleMeta' => $meta,
            'stats' => [
                self::stat('Available stock', (string) Drug::atLevel($level)->inInventory()->count(), 'Ready to dispense', 'teal', getDashboardDrugRoute('index'), 'cube'),
                self::stat('Low stock', (string) $lowStock, 'Review with manager', 'amber', getDashboardDrugRoute('index'), 'bell'),
                self::stat('Expiring soon', (string) $expiring, 'Within 6 months', 'red', getDashboardDrugRoute('index'), 'shield'),
            ],
            'alerts' => $alerts,
            'quickActions' => self::quickActions([
                ['label' => 'View inventory', 'description' => 'Check stock before dispensing', 'url' => getDashboardDrugRoute('index'), 'primary' => true, 'icon' => 'cube'],
                ['label' => 'Procurement status', 'description' => 'Track incoming supply', 'url' => getDashboardOrderRoute('index'), 'icon' => 'clipboard'],
            ]),
            'recentItems' => Drug::atLevel($level)->inInventory()->latest()->limit(4)->get()->map(fn (Drug $drug) => [
                'title' => $drug->drug_name.' ('.$drug->dosage.')',
                'subtitle' => number_format($drug->quantity_on_hand).' '.$drug->unit.' · '.$drug->storage_location,
                'meta' => 'Batch '.$drug->batch_number,
                'url' => getDashboardDrugRoute('show', $drug),
            ])->all(),
            'supplyChainHighlight' => 'modilon_hospital',
            'charts' => DashboardChartService::forRole('pharmacist', null, $level),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function stat(string $label, string $value, string $hint, string $tone, string $url, ?string $icon = null): array
    {
        return compact('label', 'value', 'hint', 'tone', 'url', 'icon');
    }

    /**
     * @param  array<int, array<string, mixed>>  $actions
     * @return array<int, array<string, mixed>>
     */
    protected static function quickActions(array $actions): array
    {
        return $actions;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function recentOrderRow(Order $order): array
    {
        return [
            'title' => $order->order_number,
            'subtitle' => $order->itemsSummary().' · '.$order->supplier,
            'meta' => ucfirst($order->status),
            'url' => getDashboardOrderRoute('show', $order),
        ];
    }
}
