<?php

namespace App\Services;

use App\Models\DiscrepancyReport;
use App\Models\HospitalOrder;
use App\Models\Order;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\Auth;

class PortalNavigationService
{
    /**
     * @return array<string, mixed>|null
     */
    public static function currentRoleMeta(): ?array
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        foreach (config('portal.roles', []) as $role => $meta) {
            if ($user->hasRole($role)) {
                return array_merge($meta, ['key' => $role]);
            }
        }

        return null;
    }

    /**
     * Role-ordered navigation groups for the sidebar.
     * Keys are display labels; values are link items in workflow order.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function sections(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $meta = self::currentRoleMeta();

        $items = match (true) {
            $user->hasRole('admin') => self::adminNav($user, $meta),
            $user->hasRole('procurement_officer') => self::procurementNav($user, $meta),
            $user->hasRole('store_manager') => self::storeManagerNav($user, $meta),
            $user->hasRole('pharmacy_manager') => self::pharmacyManagerNav($user, $meta),
            $user->hasRole('pharmacist') => self::pharmacistNav($user, $meta),
            default => [self::dashboardItem()],
        };

        return collect($items)
            ->groupBy('group')
            ->map(fn ($group) => $group->values()->all())
            ->all();
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<int, array<string, mixed>>
     */
    protected static function adminNav($user, ?array $meta): array
    {
        return array_values(array_filter([
            self::dashboardItem(),
            self::item('Procurement', 'Procurement Orders', 'Approve & receive supplier orders', getDashboardOrderRoute('index'), request()->routeIs('*.dashboard.orders.*', 'dashboard.orders.*'), 'clipboard', self::orderNavBadge($user)),
            self::item('Procurement', 'Medicine Catalog', 'Approved medicines for procurement', getDashboardMedicineRoute('index'), request()->routeIs('*.dashboard.medicines.*'), 'clipboard'),
            self::item('Inventory', 'Drug Inventory', $meta['inventory_label'] ?? 'NDoH stock batches', getDashboardDrugRoute('index'), request()->routeIs('*.dashboard.drugs.*'), 'cube'),
            self::item('Inventory', 'Stock Takes', 'Physical count and adjustments', getDashboardStockAdjustmentRoute('index'), request()->routeIs('*.dashboard.stock-adjustments.*'), 'clipboard'),
            self::item('Inventory', 'Stock Status', 'Corridor consumption & procurement suggestions', getDashboardStockStatusRoute('index'), request()->routeIs('*.dashboard.reports.stock-status.*'), 'chart'),
            self::item('Logistics', 'Shipments to Lae AMS', 'NDoH → Lae AMS logistics', getDashboardTransferRoute('index'), request()->routeIs('*.dashboard.transfers.*'), 'truck', self::shipmentNavBadge($user)),
            self::item('Reports', 'NDoH Report', 'Spend, procurement & national logistics', getDashboardNdohReportRoute('index'), request()->routeIs('*.dashboard.reports.ndoh.*'), 'chart'),
            canManageUsers()
                ? self::item('Administration', 'User Management', 'Procurement, store & pharmacy managers', getDashboardUserRoute('index'), request()->routeIs('*.dashboard.users.*'), 'users')
                : null,
        ]));
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<int, array<string, mixed>>
     */
    protected static function procurementNav($user, ?array $meta): array
    {
        return [
            self::dashboardItem(),
            self::item('Procurement', 'Procurement Orders', 'Create & track supplier orders', getDashboardOrderRoute('index'), request()->routeIs('*.dashboard.orders.*', 'dashboard.orders.*'), 'clipboard', self::orderNavBadge($user)),
            self::item('Procurement', 'Medicine Catalog', 'Approved medicines for procurement', getDashboardMedicineRoute('index'), request()->routeIs('*.dashboard.medicines.*'), 'clipboard'),
            self::item('Inventory', 'Drug Inventory', $meta['inventory_label'] ?? 'NDoH stock batches', getDashboardDrugRoute('index'), request()->routeIs('*.dashboard.drugs.*'), 'cube'),
            self::item('Inventory', 'Stock Takes', 'Physical count and adjustments', getDashboardStockAdjustmentRoute('index'), request()->routeIs('*.dashboard.stock-adjustments.*'), 'clipboard'),
            self::item('Inventory', 'Stock Status', 'Corridor consumption & procurement suggestions', getDashboardStockStatusRoute('index'), request()->routeIs('*.dashboard.reports.stock-status.*'), 'chart'),
            self::item('Logistics', 'Shipments to Lae AMS', 'NDoH → Lae AMS logistics', getDashboardTransferRoute('index'), request()->routeIs('*.dashboard.transfers.*'), 'truck', self::shipmentNavBadge($user)),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<int, array<string, mixed>>
     */
    protected static function storeManagerNav($user, ?array $meta): array
    {
        $pendingHospital = HospitalOrder::pending()->count();
        $openDiscrepancies = DiscrepancyReport::open()->count();

        return [
            self::dashboardItem(),
            self::item('Warehouse ops', 'Hospital Orders', 'Approve & ship Modilon requests', getDashboardHospitalOrderRoute('index'), request()->routeIs('*.dashboard.hospital-orders.*'), 'clipboard', $pendingHospital > 0 ? $pendingHospital : null),
            self::item('Warehouse ops', 'NDoH Shipments', 'Confirm incoming national stock', getDashboardTransferRoute('index'), request()->routeIs('*.dashboard.transfers.*'), 'truck', self::shipmentNavBadge($user)),
            self::item('Warehouse ops', 'Hospital Road Deliveries', 'Lae AMS → Modilon by car', getDashboardHospitalShipmentRoute('index'), request()->routeIs('*.dashboard.hospital-shipments.*'), 'truck'),
            self::item('Inventory', 'Drug Inventory', $meta['inventory_label'] ?? 'Lae AMS stock', getDashboardDrugRoute('index'), request()->routeIs('*.dashboard.drugs.*'), 'cube'),
            self::item('Inventory', 'Stock Takes', 'Physical count and adjustments', getDashboardStockAdjustmentRoute('index'), request()->routeIs('*.dashboard.stock-adjustments.*'), 'clipboard'),
            self::item('Inventory', 'Stock Status', 'Consumption, days of stock, suggestions', getDashboardStockStatusRoute('index'), request()->routeIs('*.dashboard.reports.stock-status.*'), 'chart'),
            self::item('Reports', 'Regional Report', 'Generate Lae AMS period summary', getDashboardRegionalReportRoute('index'), request()->routeIs('*.dashboard.reports.regional.*'), 'chart'),
            self::item('Reports', 'Discrepancy Reports', 'Hospital receipt issues', getDashboardDiscrepancyRoute('index'), request()->routeIs('*.dashboard.discrepancies.*'), 'clipboard', $openDiscrepancies > 0 ? $openDiscrepancies : null),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<int, array<string, mixed>>
     */
    protected static function pharmacyManagerNav($user, ?array $meta): array
    {
        return array_values(array_filter([
            self::dashboardItem(),
            self::item('Supply', 'Hospital Orders', 'Request stock from Lae AMS', getDashboardHospitalOrderRoute('index'), request()->routeIs('*.dashboard.hospital-orders.*'), 'clipboard'),
            self::item('Supply', 'Incoming Deliveries', 'Verify Lae AMS road deliveries', getDashboardHospitalShipmentRoute('index'), request()->routeIs('*.dashboard.hospital-shipments.*'), 'truck'),
            self::item('Supply', 'Discrepancy Reports', 'Report receipt issues', getDashboardDiscrepancyRoute('index'), request()->routeIs('*.dashboard.discrepancies.*'), 'clipboard'),
            self::item('Inventory', 'Drug Inventory', $meta['inventory_label'] ?? 'Modilon stock', getDashboardDrugRoute('index'), request()->routeIs('*.dashboard.drugs.*'), 'cube'),
            self::item('Inventory', 'Stock Takes', 'Physical count and adjustments', getDashboardStockAdjustmentRoute('index'), request()->routeIs('*.dashboard.stock-adjustments.*'), 'clipboard'),
            self::item('Inventory', 'Stock Status', 'Consumption & suggested requests', getDashboardStockStatusRoute('index'), request()->routeIs('*.dashboard.reports.stock-status.*'), 'chart'),
            self::item('Pharmacy', 'Patients', 'Modilon patient register', getDashboardPatientRoute('index'), request()->routeIs('*.dashboard.patients.*'), 'users'),
            self::item('Pharmacy', 'Dispensing', 'Review dispensed medicines', getDashboardDispensingRoute('index'), request()->routeIs('*.dashboard.dispensing.*'), 'pill'),
            self::item('Reports', 'Hospital Report', 'Generate Modilon period summary', getDashboardHospitalReportRoute('index'), request()->routeIs('*.dashboard.reports.hospital.*'), 'chart'),
            self::item('National', 'Procurement Orders', 'Track national supply status', getDashboardOrderRoute('index'), request()->routeIs('*.dashboard.orders.*', 'dashboard.orders.*'), 'clipboard'),
            canManageUsers()
                ? self::item('Administration', 'User Management', 'Pharmacist accounts', getDashboardUserRoute('index'), request()->routeIs('*.dashboard.users.*'), 'users')
                : null,
        ]));
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<int, array<string, mixed>>
     */
    protected static function pharmacistNav($user, ?array $meta): array
    {
        return [
            self::dashboardItem(),
            self::item('Dispensing', 'Dispense Medicine', 'Audit Rx and issue Modilon stock', getDashboardDispensingRoute('index'), request()->routeIs('*.dashboard.dispensing.*'), 'pill'),
            self::item('Dispensing', 'Patients', 'Register and look up patients', getDashboardPatientRoute('index'), request()->routeIs('*.dashboard.patients.*'), 'users'),
            self::item('Stock', 'Drug Inventory', 'Batch, quantity, and expiry', getDashboardDrugRoute('index'), request()->routeIs('*.dashboard.drugs.*'), 'cube'),
            self::item('Stock', 'Stock Status', 'Modilon consumption & days of stock', getDashboardStockStatusRoute('index'), request()->routeIs('*.dashboard.reports.stock-status.*'), 'chart'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function dashboardItem(): array
    {
        return self::item(
            'Overview',
            'Dashboard',
            'Overview & alerts',
            getRoleDashboardRoute(),
            request()->routeIs(
                'dashboard',
                'dashboard.admin',
                'dashboard.procurement_officer',
                'dashboard.store_manager',
                'dashboard.pharmacy_manager',
                'dashboard.pharmacist',
            ),
            'home',
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected static function item(
        string $group,
        string $label,
        string $description,
        string $href,
        bool $active,
        string $icon,
        ?int $badge = null,
    ): array {
        return compact('group', 'label', 'description', 'href', 'active', 'icon', 'badge');
    }

    protected static function orderNavBadge($user): ?int
    {
        if ($user->hasRole('admin')) {
            $count = Order::pending()->count();

            return $count > 0 ? $count : null;
        }

        if ($user->hasRole('procurement_officer')) {
            $count = Order::pending()->where('created_by', $user->id)->count();

            return $count > 0 ? $count : null;
        }

        return null;
    }

    protected static function shipmentNavBadge($user): ?int
    {
        if ($user->hasRole('admin')) {
            $count = StockTransfer::pending()->fromLevel('ndoh')->toLevel('lae_ams')->whereNull('hospital_order_id')->count();

            return $count > 0 ? $count : null;
        }

        if ($user->hasRole('procurement_officer')) {
            $count = StockTransfer::pending()->sentBy($user->id)->whereNull('hospital_order_id')->count();

            return $count > 0 ? $count : null;
        }

        if ($user->hasRole('store_manager')) {
            $count = StockTransfer::sent()->toLevel('lae_ams')->whereNull('hospital_order_id')->count();

            return $count > 0 ? $count : null;
        }

        return null;
    }

    /**
     * Primary header action for the top bar (role-specific).
     *
     * @return array{label: string, url: string}|null
     */
    public static function primaryAction(): ?array
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->hasRole('procurement_officer')) {
            return ['label' => 'New order', 'url' => getDashboardOrderRoute('create')];
        }

        if ($user->hasRole('admin')) {
            return ['label' => 'Review orders', 'url' => getDashboardOrderRoute('index')];
        }

        if ($user->hasRole('pharmacy_manager')) {
            return ['label' => 'New request', 'url' => getDashboardHospitalOrderRoute('create')];
        }

        if ($user->hasRole('store_manager')) {
            return ['label' => 'Hospital orders', 'url' => getDashboardHospitalOrderRoute('index')];
        }

        if ($user->hasRole('pharmacist')) {
            return ['label' => 'Dispense', 'url' => getDashboardDispensingRoute('create')];
        }

        return null;
    }
}
