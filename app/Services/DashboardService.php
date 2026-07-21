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
                self::stat('Pending approvals', (string) $pendingCount, 'Orders awaiting your sign-off', 'amber', getDashboardOrderRoute('index').'?status=pending'),
                self::stat('NDoH stock batches', (string) Drug::atLevel($level)->inInventory()->count(), 'Active inventory at central store', 'teal', getDashboardDrugRoute('index')),
                self::stat('In-transit shipments', (string) StockTransfer::sent()->toLevel('lae_ams')->count(), 'Awaiting Lae AMS receipt', 'blue', getDashboardTransferRoute('index').'?status=sent'),
                self::stat('Unread alerts', (string) $user->unreadNotifications()->count(), 'New system notifications', 'slate', getDashboardOrderRoute('index')),
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
                    'subtitle' => ($order->drug->drug_name ?? 'Unknown drug').' · '.number_format($order->quantity_ordered).' units · '.$order->supplier,
                    'url' => getDashboardOrderRoute('show', $order),
                    'action' => 'Approve',
                ])->all(),
            ]] : [],
            'quickActions' => self::quickActions([
                ['label' => 'Review orders', 'description' => 'Approve pending procurement', 'url' => getDashboardOrderRoute('index'), 'primary' => true],
                ['label' => 'Drug inventory', 'description' => 'NDoH central stock', 'url' => getDashboardDrugRoute('index')],
                ['label' => 'Track shipments', 'description' => 'NDoH → Lae AMS logistics', 'url' => getDashboardTransferRoute('index')],
            ]),
            'recentItems' => Order::latest()->limit(5)->get()->map(fn (Order $order) => self::recentOrderRow($order))->all(),
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
                self::stat('My pending orders', (string) $myPending, 'Awaiting NDoH approval', 'amber', getDashboardOrderRoute('index').'?status=pending'),
                self::stat('Total orders', (string) $myOrders, 'All procurement requests', 'teal', getDashboardOrderRoute('index')),
                self::stat('NDoH drug batches', (string) Drug::atLevel($level)->count(), 'Registered medicine types', 'slate', getDashboardDrugRoute('index')),
                self::stat('Shipments in transit', (string) StockTransfer::sent()->fromLevel('ndoh')->count(), 'Sent to Lae AMS', 'blue', getDashboardTransferRoute('index')),
            ],
            'alerts' => [],
            'quickActions' => self::quickActions([
                ['label' => 'New order', 'description' => 'Compare suppliers & create PO', 'url' => getDashboardOrderRoute('create'), 'primary' => true],
                ['label' => 'My orders', 'description' => 'Track approval status', 'url' => getDashboardOrderRoute('index')],
                ['label' => 'Record shipment', 'description' => 'Send stock to Lae AMS', 'url' => getDashboardTransferRoute('create')],
                ['label' => 'Drug catalog', 'description' => 'NDoH medicine types', 'url' => getDashboardDrugRoute('index')],
            ]),
            'recentItems' => Order::where('created_by', $user->id)->latest()->limit(5)->get()->map(fn (Order $order) => self::recentOrderRow($order))->all(),
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
        $lowStock = Drug::atLevel($level)->lowStock()->count();

        $alerts = [];

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
                self::stat('Awaiting receipt', (string) $pendingShipments, 'Incoming from NDoH', 'blue', getDashboardTransferRoute('index').'?status=sent'),
                self::stat('Lae AMS batches', (string) Drug::atLevel($level)->inInventory()->count(), 'Warehouse stock on hand', 'teal', getDashboardDrugRoute('index')),
                self::stat('Low stock alerts', (string) $lowStock, 'Below reorder point', 'amber', getDashboardDrugRoute('index').'?filter=low_stock'),
                self::stat('Expiring soon', (string) Drug::atLevel($level)->expiring()->count(), 'Within 90 days', 'red', getDashboardDrugRoute('index').'?filter=expiring'),
            ],
            'alerts' => $alerts,
            'quickActions' => self::quickActions([
                ['label' => 'Confirm shipments', 'description' => 'Receive NDoH deliveries', 'url' => getDashboardTransferRoute('index'), 'primary' => true],
                ['label' => 'Warehouse stock', 'description' => 'Lae AMS inventory', 'url' => getDashboardDrugRoute('index')],
                ['label' => 'View orders', 'description' => 'Upstream procurement', 'url' => getDashboardOrderRoute('index')],
            ]),
            'recentItems' => StockTransfer::toLevel('lae_ams')->latest()->limit(5)->get()->map(fn (StockTransfer $transfer) => [
                'title' => $transfer->transfer_number,
                'subtitle' => ($transfer->drug->drug_name ?? 'Unknown').' · '.number_format($transfer->quantity_sent).' units',
                'meta' => ucfirst($transfer->status),
                'url' => getDashboardTransferRoute('show', $transfer),
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

        return [
            'roleMeta' => $meta,
            'stats' => [
                self::stat('Hospital batches', (string) Drug::atLevel($level)->inInventory()->count(), 'Modilon pharmacy stock', 'teal', getDashboardDrugRoute('index')),
                self::stat('Low stock', (string) Drug::atLevel($level)->lowStock()->count(), 'Needs replenishment', 'amber', getDashboardDrugRoute('index')),
                self::stat('Expiring soon', (string) Drug::atLevel($level)->expiring()->count(), 'Review before expiry', 'red', getDashboardDrugRoute('index')),
                self::stat('Procurement orders', (string) Order::received()->count(), 'Upstream supply reference', 'slate', getDashboardOrderRoute('index')),
            ],
            'alerts' => [],
            'quickActions' => self::quickActions([
                ['label' => 'Hospital inventory', 'description' => 'Manage Modilon stock', 'url' => getDashboardDrugRoute('index'), 'primary' => true],
                ['label' => 'Track orders', 'description' => 'National procurement status', 'url' => getDashboardOrderRoute('index')],
            ]),
            'recentItems' => Drug::atLevel($level)->latest()->limit(5)->get()->map(fn (Drug $drug) => [
                'title' => $drug->drug_name.' ('.$drug->dosage.')',
                'subtitle' => 'Batch '.$drug->batch_number.' · '.number_format($drug->quantity_on_hand).' on hand',
                'meta' => ucfirst(str_replace('_', ' ', $drug->status ?? 'active')),
                'url' => getDashboardDrugRoute('show', $drug),
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

        return [
            'roleMeta' => $meta,
            'stats' => [
                self::stat('Available batches', (string) Drug::atLevel($level)->inInventory()->count(), 'Ready for dispensing', 'teal', getDashboardDrugRoute('index')),
                self::stat('Low stock', (string) Drug::atLevel($level)->lowStock()->count(), 'Inform pharmacy manager', 'amber', getDashboardDrugRoute('index')),
                self::stat('Expiring soon', (string) Drug::atLevel($level)->expiring()->count(), 'Use or quarantine', 'red', getDashboardDrugRoute('index')),
                self::stat('Order reference', (string) Order::whereIn('status', ['ordered', 'shipped', 'received'])->count(), 'Supply pipeline', 'slate', getDashboardOrderRoute('index')),
            ],
            'alerts' => [],
            'quickActions' => self::quickActions([
                ['label' => 'View inventory', 'description' => 'Check stock before dispensing', 'url' => getDashboardDrugRoute('index'), 'primary' => true],
                ['label' => 'Procurement status', 'description' => 'Track incoming supply', 'url' => getDashboardOrderRoute('index')],
            ]),
            'recentItems' => Drug::atLevel($level)->inInventory()->latest()->limit(5)->get()->map(fn (Drug $drug) => [
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
    protected static function stat(string $label, string $value, string $hint, string $tone, string $url): array
    {
        return compact('label', 'value', 'hint', 'tone', 'url');
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
            'subtitle' => ($order->drug->drug_name ?? 'Unknown drug').' · '.$order->supplier,
            'meta' => ucfirst($order->status),
            'url' => getDashboardOrderRoute('show', $order),
        ];
    }
}
