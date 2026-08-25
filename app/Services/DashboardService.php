<?php

namespace App\Services;

use App\Models\DispensingRecord;
use App\Models\DiscrepancyReport;
use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\Order;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\User;
use Carbon\Carbon;

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
            'quickActionGroups' => [],
            'quickActions' => [],
            'recentItems' => [],
            'recentTitle' => 'Recent activity',
            'charts' => [],
            'insights' => [
                'stockHealth' => null,
                'atRisk' => null,
                'expiry' => null,
                'dispenseTrend' => null,
                'shipments' => null,
            ],
            'supplyOverview' => null,
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
        $pendingShipments = StockTransfer::pending()->fromLevel('ndoh')->toLevel('lae_ams')->whereNull('hospital_order_id')->count();
        $pipelineCounts = Order::pipelineCounts();
        $alerts = [];

        if ($pendingCount > 0) {
            $alerts[] = [
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
            ];
        }

        if ($pendingShipments > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Pending shipment approvals',
                'message' => $pendingShipments === 1
                    ? '1 shipment to Lae AMS needs your approval before stock is sent.'
                    : "{$pendingShipments} shipments to Lae AMS need your approval before stock is sent.",
                'action_label' => 'Review shipments',
                'action_url' => getDashboardTransferRoute('index').'?status=pending',
                'items' => TransferNotificationService::pendingShipmentsForAdmin()->map(fn (StockTransfer $transfer) => [
                    'title' => $transfer->transfer_number,
                    'subtitle' => $transfer->medicinesLabel().' · '.number_format($transfer->quantity_sent).' units · '.($transfer->sender->name ?? 'Procurement'),
                    'url' => getDashboardTransferRoute('show', $transfer),
                    'action' => 'Approve',
                ])->all(),
            ];
        }

        return [
            'roleMeta' => $meta,
            'pipelineCounts' => $pipelineCounts,
            'stats' => self::stockKpis(
                $level,
                $pendingShipments + StockTransfer::sent()->toLevel('lae_ams')->whereNull('hospital_order_id')->count(),
                'NDoH → Lae AMS unfinished',
                getDashboardTransferRoute('index'),
            ),
            'alerts' => $alerts,
            'quickActionGroups' => [
                self::actionGroup('Procurement', [
                    ['label' => 'Review orders', 'description' => 'Approve pending procurement', 'url' => getDashboardOrderRoute('index'), 'primary' => true, 'icon' => 'clipboard'],
                    ['label' => 'Medicine catalog', 'description' => 'Approved medicines list', 'url' => getDashboardMedicineRoute('index'), 'icon' => 'clipboard'],
                ]),
                self::actionGroup('Inventory & logistics', [
                    ['label' => 'Drug inventory', 'description' => 'NDoH central stock', 'url' => getDashboardDrugRoute('index'), 'icon' => 'cube'],
                    ['label' => 'Stock status', 'description' => 'Corridor consumption & suggestions', 'url' => getDashboardStockStatusRoute('index'), 'icon' => 'chart'],
                    ['label' => 'Shipments to Lae AMS', 'description' => 'NDoH → Lae AMS logistics', 'url' => getDashboardTransferRoute('index'), 'icon' => 'truck'],
                ]),
                self::actionGroup('Reports & admin', array_values(array_filter([
                    ['label' => 'NDoH report', 'description' => 'Spend & national summary', 'url' => getDashboardNdohReportRoute('index'), 'icon' => 'chart'],
                    canManageUsers() ? ['label' => 'User management', 'description' => 'Manage portal accounts', 'url' => getDashboardUserRoute('index'), 'icon' => 'shield'] : null,
                ]))),
            ],
            'quickActions' => [],
            'recentItems' => Order::with(['items.drug', 'drug'])->latest()->limit(4)->get()->map(fn (Order $order) => self::recentOrderRow($order))->all(),
            'recentTitle' => 'Recent procurement orders',
            'charts' => DashboardChartService::forRole('admin'),
            'insights' => DashboardInsightService::forRole('admin', $level),
            'supplyOverview' => DashboardChartService::supplyOverview('admin', $level),
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected static function procurementPayload(User $user, array $meta): array
    {
        $pipelineCounts = Order::pipelineCounts($user->id);
        $procurementNeeds = LmisService::procurementSuggestions()
            ->filter(fn (array $row) => $row['suggested_quantity'] > 0 && in_array($row['status'], ['stock_out', 'critical', 'low'], true))
            ->count();

        $alerts = [];
        if ($procurementNeeds > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Procurement suggestions ready',
                'message' => $procurementNeeds === 1
                    ? '1 medicine needs restocking based on Modilon consumption and corridor stock.'
                    : "{$procurementNeeds} medicines need restocking based on Modilon consumption and corridor stock.",
                'action_label' => 'Stock status',
                'action_url' => getDashboardStockStatusRoute('index').'?level=corridor',
            ];
        }

        return [
            'roleMeta' => $meta,
            'pipelineCounts' => $pipelineCounts,
            'stats' => self::stockKpis(
                $meta['inventory_level'],
                StockTransfer::query()->fromLevel('ndoh')->toLevel('lae_ams')->whereNull('hospital_order_id')->whereIn('status', ['pending', 'sent'])->count(),
                'NDoH → Lae AMS unfinished',
                getDashboardTransferRoute('index'),
            ),
            'alerts' => $alerts,
            'quickActionGroups' => [
                self::actionGroup('Procurement', [
                    ['label' => 'New order', 'description' => 'Create procurement order', 'url' => getDashboardOrderRoute('create'), 'primary' => true, 'icon' => 'plus'],
                    ['label' => 'My orders', 'description' => 'Track approval status', 'url' => getDashboardOrderRoute('index'), 'icon' => 'clipboard'],
                    ['label' => 'Medicine catalog', 'description' => 'Approved medicines list', 'url' => getDashboardMedicineRoute('index'), 'icon' => 'clipboard'],
                ]),
                self::actionGroup('Inventory & logistics', [
                    ['label' => 'Drug inventory', 'description' => 'NDoH central stock', 'url' => getDashboardDrugRoute('index'), 'icon' => 'cube'],
                    ['label' => 'Ship to Lae AMS', 'description' => 'Record shipment (admin approves)', 'url' => getDashboardTransferRoute('create'), 'icon' => 'truck'],
                    ['label' => 'Stock status', 'description' => 'Corridor consumption & suggestions', 'url' => getDashboardStockStatusRoute('index'), 'icon' => 'chart'],
                ]),
            ],
            'quickActions' => [],
            'recentItems' => Order::where('created_by', $user->id)->latest()->limit(4)->get()->map(fn (Order $order) => self::recentOrderRow($order))->all(),
            'recentTitle' => 'My recent orders',
            'charts' => DashboardChartService::forRole('procurement_officer', $user->id),
            'insights' => DashboardInsightService::forRole('procurement_officer', $meta['inventory_level'], $user->id),
            'supplyOverview' => DashboardChartService::supplyOverview('procurement_officer', $meta['inventory_level'], $user->id),
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected static function storeManagerPayload(User $user, array $meta): array
    {
        $level = $meta['inventory_level'];
        $pendingShipments = StockTransfer::sent()->toLevel('lae_ams')->whereNull('hospital_order_id')->count();
        $pendingHospitalOrders = HospitalOrder::pending()->count();
        $lowStock = Drug::atLevel($level)->lowStock()->count();
        $openDiscrepancies = DiscrepancyReport::open()->count();
        $lmisCounts = LmisService::statusCountsForLevel($level);
        $atRisk = $lmisCounts['stock_out'] + $lmisCounts['critical'];

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
                    'subtitle' => $transfer->medicinesLabel().' · '.number_format($transfer->quantity_sent).' units · '.$transfer->lineCount().' batch'.($transfer->lineCount() === 1 ? '' : 'es'),
                    'url' => getDashboardTransferRoute('show', $transfer),
                    'action' => 'Confirm',
                ])->all(),
            ];
        }

        if ($atRisk > 0) {
            $alerts[] = [
                'tone' => 'red',
                'title' => 'Stock-out risk at Lae AMS',
                'message' => $atRisk === 1
                    ? '1 medicine is stocked out or below 2 weeks of cover based on warehouse issues.'
                    : "{$atRisk} medicines are stocked out or below 2 weeks of cover based on warehouse issues.",
                'action_label' => 'Stock status',
                'action_url' => getDashboardStockStatusRoute('index').'?level=lae_ams',
            ];
        } elseif ($lowStock > 0) {
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

        if ($openDiscrepancies > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Open discrepancy reports',
                'message' => $openDiscrepancies === 1
                    ? '1 hospital receipt issue is still open.'
                    : "{$openDiscrepancies} hospital receipt issues are still open.",
                'action_label' => 'View discrepancies',
                'action_url' => getDashboardDiscrepancyRoute('index'),
            ];
        }

        return [
            'roleMeta' => $meta,
            'stats' => self::stockKpis(
                $level,
                HospitalOrder::query()->whereIn('status', ['pending', 'approved', 'shipped'])->count(),
                'Hospital orders not yet received',
                getDashboardHospitalOrderRoute('index'),
            ),
            'alerts' => $alerts,
            'quickActionGroups' => [
                self::actionGroup('Warehouse ops', [
                    ['label' => 'Hospital orders', 'description' => 'Approve & ship Modilon requests', 'url' => getDashboardHospitalOrderRoute('index'), 'primary' => true, 'icon' => 'hospital'],
                    ['label' => 'Confirm NDoH receipts', 'description' => 'Receive national shipments', 'url' => getDashboardTransferRoute('index'), 'icon' => 'truck'],
                    ['label' => 'Road deliveries', 'description' => 'Lae AMS → Modilon by car', 'url' => getDashboardHospitalShipmentRoute('index'), 'icon' => 'truck'],
                ]),
                self::actionGroup('Inventory', [
                    ['label' => 'Drug inventory', 'description' => 'Lae AMS warehouse stock', 'url' => getDashboardDrugRoute('index'), 'icon' => 'cube'],
                    ['label' => 'Stock status', 'description' => 'Consumption & days of stock', 'url' => getDashboardStockStatusRoute('index'), 'icon' => 'chart'],
                ]),
                self::actionGroup('Reports', [
                    ['label' => 'Regional report', 'description' => 'Generate Lae AMS summary', 'url' => getDashboardRegionalReportRoute('index'), 'icon' => 'chart'],
                    ['label' => 'Discrepancies', 'description' => 'Hospital receipt issues', 'url' => getDashboardDiscrepancyRoute('index'), 'icon' => 'clipboard'],
                ]),
            ],
            'quickActions' => [],
            'recentItems' => HospitalOrder::latest()->limit(4)->get()->map(fn ($order) => [
                'title' => $order->order_number,
                'subtitle' => $order->drug_name.' · '.number_format($order->quantity_requested).' units requested',
                'meta' => hospitalOrderStatusLabel($order->status),
                'url' => getDashboardHospitalOrderRoute('show', $order),
            ])->all(),
            'recentTitle' => 'Recent hospital orders',
            'charts' => DashboardChartService::forRole('store_manager', null, $level),
            'insights' => DashboardInsightService::forRole('store_manager', $level),
            'supplyOverview' => DashboardChartService::supplyOverview('store_manager', $level),
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
        $pendingRequests = HospitalOrder::where('requested_by', $user->id)->pending()->count();
        $incomingShipments = HospitalOrder::where('requested_by', $user->id)->where('status', 'shipped')->count();
        $lmisCounts = LmisService::statusCountsForLevel($level);
        $atRisk = $lmisCounts['stock_out'] + $lmisCounts['critical'];

        $alerts = [];

        if ($atRisk > 0) {
            $alerts[] = [
                'tone' => 'red',
                'title' => 'Stock-out risk at Modilon',
                'message' => $atRisk === 1
                    ? '1 medicine is stocked out or has under 2 weeks of cover from recent dispensing.'
                    : "{$atRisk} medicines are stocked out or have under 2 weeks of cover from recent dispensing.",
                'action_label' => 'Request stock',
                'action_url' => getDashboardHospitalOrderRoute('create'),
            ];
        } elseif ($lowStock > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Low stock at Modilon Hospital',
                'message' => $lowStock === 1
                    ? '1 medicine batch needs replenishment.'
                    : "{$lowStock} medicine batches need replenishment.",
                'action_label' => 'View inventory',
                'action_url' => getDashboardDrugRoute('index').'?status=low_stock',
            ];
        }

        if ($incomingShipments > 0) {
            $alerts[] = [
                'tone' => 'blue',
                'title' => 'Deliveries to receive',
                'message' => $incomingShipments === 1
                    ? '1 Lae AMS road delivery is awaiting verification at Modilon.'
                    : "{$incomingShipments} Lae AMS road deliveries are awaiting verification at Modilon.",
                'action_label' => 'Incoming deliveries',
                'action_url' => getDashboardHospitalShipmentRoute('index'),
            ];
        }

        $lastRequestAt = HospitalOrder::where('requested_by', $user->id)->latest('created_at')->value('created_at');
        if (! $lastRequestAt || Carbon::parse($lastRequestAt)->lt(now()->subDays(30))) {
            $alerts[] = [
                'tone' => 'blue',
                'title' => 'Monthly Lae AMS request due',
                'message' => 'No hospital stock request has been submitted in the last 30 days. Review Stock Status and file your monthly requisition.',
                'action_label' => 'New request',
                'action_url' => getDashboardHospitalOrderRoute('create'),
            ];
        }

        $lastStockTakeAt = StockAdjustment::atLevel($level)->latest('adjusted_at')->value('adjusted_at');
        if (! $lastStockTakeAt || Carbon::parse($lastStockTakeAt)->lt(now()->subDays(30))) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Monthly stock take due',
                'message' => 'No Modilon stock take has been posted in the last 30 days. Complete a physical count to keep inventory accurate.',
                'action_label' => 'Stock takes',
                'action_url' => getDashboardStockAdjustmentRoute('index'),
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
                'action_url' => getDashboardDrugRoute('index').'?status=expiring_soon',
            ];
        }

        return [
            'roleMeta' => $meta,
            'stats' => self::stockKpis(
                $level,
                $incomingShipments,
                'Road deliveries in transit',
                getDashboardHospitalShipmentRoute('index'),
            ),
            'alerts' => $alerts,
            'quickActionGroups' => [
                self::actionGroup('Supply', [
                    ['label' => 'Request from Lae AMS', 'description' => 'Submit hospital order', 'url' => getDashboardHospitalOrderRoute('create'), 'primary' => true, 'icon' => 'plus'],
                    ['label' => 'Incoming deliveries', 'description' => 'Verify Lae AMS road deliveries', 'url' => getDashboardHospitalShipmentRoute('index'), 'icon' => 'truck'],
                    ['label' => 'Discrepancy reports', 'description' => 'Report receipt issues', 'url' => getDashboardDiscrepancyRoute('index'), 'icon' => 'clipboard'],
                ]),
                self::actionGroup('Inventory', [
                    ['label' => 'Hospital inventory', 'description' => 'Manage Modilon stock', 'url' => getDashboardDrugRoute('index'), 'icon' => 'cube'],
                    ['label' => 'Stock takes', 'description' => 'Physical count and adjustments', 'url' => getDashboardStockAdjustmentRoute('index'), 'icon' => 'clipboard'],
                    ['label' => 'Stock status', 'description' => 'Consumption & suggested qty', 'url' => getDashboardStockStatusRoute('index'), 'icon' => 'chart'],
                ]),
                self::actionGroup('Pharmacy & reports', array_values(array_filter([
                    ['label' => 'Patients', 'description' => 'Modilon patient register', 'url' => getDashboardPatientRoute('index'), 'icon' => 'users'],
                    ['label' => 'Hospital report', 'description' => 'Generate Modilon summary', 'url' => getDashboardHospitalReportRoute('index'), 'icon' => 'chart'],
                    canManageUsers() ? ['label' => 'Manage pharmacists', 'description' => 'Pharmacy team accounts', 'url' => getDashboardUserRoute('index'), 'icon' => 'shield'] : null,
                ]))),
            ],
            'quickActions' => [],
            'recentItems' => HospitalOrder::where('requested_by', $user->id)->latest()->limit(4)->get()->map(fn ($order) => [
                'title' => $order->order_number,
                'subtitle' => $order->drug_name.' · '.number_format($order->quantity_requested).' units requested',
                'meta' => hospitalOrderStatusLabel($order->status),
                'url' => getDashboardHospitalOrderRoute('show', $order),
            ])->all(),
            'recentTitle' => 'My recent requests',
            'charts' => DashboardChartService::forRole('pharmacy_manager', null, $level),
            'insights' => DashboardInsightService::forRole('pharmacy_manager', $level, $user->id),
            'supplyOverview' => DashboardChartService::supplyOverview('pharmacy_manager', $level, $user->id),
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

        if ($expiring > 0) {
            $alerts[] = [
                'tone' => 'blue',
                'title' => 'Expiry alert',
                'message' => $expiring === 1
                    ? '1 Modilon batch expires within 6 months. Prefer FEFO when dispensing.'
                    : "{$expiring} Modilon batches expire within 6 months. Prefer FEFO when dispensing.",
                'action_label' => 'View expiry alerts',
                'action_url' => getDashboardDrugRoute('index').'?status=expiring_soon',
            ];
        }

        if ($lowStock > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => 'Check low stock before dispensing',
                'message' => $lowStock === 1
                    ? '1 batch is below reorder point — flag to the Pharmacy Manager.'
                    : "{$lowStock} batches are below reorder point — flag to the Pharmacy Manager.",
                'action_label' => 'View low stock',
                'action_url' => getDashboardDrugRoute('index').'?status=low_stock',
            ];
        }

        return [
            'roleMeta' => $meta,
            'stats' => self::stockKpis(
                $level,
                HospitalOrder::query()->where('status', 'shipped')->count(),
                'Road deliveries inbound',
                getDashboardDrugRoute('index'),
            ),
            'alerts' => $alerts,
            'quickActionGroups' => [
                self::actionGroup('Dispensing', [
                    ['label' => 'Dispense medicine', 'description' => 'Audit Rx then issue Modilon stock', 'url' => getDashboardDispensingRoute('create'), 'primary' => true, 'icon' => 'pill'],
                    ['label' => 'My dispensing records', 'description' => 'Your patient dispensing audit trail', 'url' => getDashboardDispensingRoute('index'), 'icon' => 'clipboard'],
                    ['label' => 'Patients', 'description' => 'Register or look up patients', 'url' => getDashboardPatientRoute('index'), 'icon' => 'users'],
                ]),
                self::actionGroup('Stock', [
                    ['label' => 'Drug inventory', 'description' => 'Batch, stock and expiry', 'url' => getDashboardDrugRoute('index'), 'icon' => 'cube'],
                    ['label' => 'Stock status', 'description' => 'Modilon consumption & days of stock', 'url' => getDashboardStockStatusRoute('index'), 'icon' => 'chart'],
                ]),
            ],
            'quickActions' => [],
            'recentItems' => DispensingRecord::with(['patient', 'drug'])
                ->where('dispensed_by', $user->id)
                ->latest('dispensed_at')
                ->limit(4)
                ->get()
                ->map(fn (DispensingRecord $record) => [
                    'title' => $record->record_number,
                    'subtitle' => ($record->patient->full_name ?? 'Patient').' · '.($record->drug->drug_name ?? 'Medicine'),
                    'meta' => $record->dispensed_at->format('d M Y H:i'),
                    'url' => getDashboardDispensingRoute('show', $record),
                ])->all(),
            'recentTitle' => 'My recent dispenses',
            'charts' => DashboardChartService::forRole('pharmacist', null, $level),
            'insights' => DashboardInsightService::forRole('pharmacist', $level, $user->id),
            'supplyOverview' => DashboardChartService::supplyOverview('pharmacist', $level, $user->id),
        ];
    }

    /**
     * Shared KPI row: batches, low stock, expiring, pending shipments.
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function stockKpis(string $level, int $pendingShipments, string $pendingHint, string $pendingUrl): array
    {
        return [
            self::stat('Drugs tracked', (string) Drug::atLevel($level)->inInventory()->count(), 'Active batches', 'teal', getDashboardDrugRoute('index'), 'cube'),
            self::stat('Low stock', (string) Drug::atLevel($level)->inInventory()->lowStock()->count(), 'At or below reorder point', 'amber', getDashboardDrugRoute('index').'?status=low_stock', 'bell'),
            self::stat('Expiring soon', (string) Drug::atLevel($level)->inInventory()->expiring()->count(), 'Within 6 months', 'red', getDashboardDrugRoute('index').'?status=expiring_soon', 'shield'),
            self::stat('Pending shipments', (string) $pendingShipments, $pendingHint, 'blue', $pendingUrl, 'truck'),
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
     * @return array{label: string, actions: array<int, array<string, mixed>>}
     */
    protected static function actionGroup(string $label, array $actions): array
    {
        return [
            'label' => $label,
            'actions' => $actions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function recentOrderRow(Order $order): array
    {
        return [
            'title' => $order->order_number,
            'subtitle' => $order->itemsSummary().' · '.$order->supplier,
            'meta' => $order->statusLabel(),
            'url' => getDashboardOrderRoute('show', $order),
        ];
    }
}
