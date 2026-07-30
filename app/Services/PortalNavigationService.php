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
     * Navigation grouped by section for the authenticated user.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function sections(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $items = [];

        $items[] = self::item(
            section: 'overview',
            label: 'Dashboard',
            description: 'Overview & alerts',
            href: getRoleDashboardRoute(),
            active: request()->routeIs(
                'dashboard',
                'dashboard.admin',
                'dashboard.procurement_officer',
                'dashboard.store_manager',
                'dashboard.pharmacy_manager',
                'dashboard.pharmacist',
            ),
            icon: 'home',
        );

        if ($user->hasAnyRole(['admin', 'procurement_officer'])) {
            $items[] = self::item(
                section: 'procurement',
                label: 'Medicine Catalog',
                description: 'Approved medicines for procurement',
                href: getDashboardMedicineRoute('index'),
                active: request()->routeIs('*.dashboard.medicines.*'),
                icon: 'clipboard',
            );
        }

        if ($user->hasAnyRole(['admin', 'procurement_officer', 'store_manager', 'pharmacy_manager', 'pharmacist'])) {
            $meta = self::currentRoleMeta();

            $items[] = self::item(
                section: 'inventory',
                label: 'Drug Inventory',
                description: $meta['inventory_label'] ?? 'Stock batches',
                href: getDashboardDrugRoute('index'),
                active: request()->routeIs('*.dashboard.drugs.*'),
                icon: 'cube',
            );

            if ($user->hasRole('store_manager')) {
                $pendingHospital = HospitalOrder::pending()->count();

                $items[] = self::item(
                    section: 'hospital',
                    label: 'Hospital Orders',
                    description: 'Modilon requests from Lae AMS',
                    href: getDashboardHospitalOrderRoute('index'),
                    active: request()->routeIs('*.dashboard.hospital-orders.*'),
                    icon: 'clipboard',
                    badge: $pendingHospital > 0 ? $pendingHospital : null,
                );

                $items[] = self::item(
                    section: 'logistics',
                    label: 'NDoH Shipments',
                    description: 'Confirm incoming national stock',
                    href: getDashboardTransferRoute('index'),
                    active: request()->routeIs('*.dashboard.transfers.*'),
                    icon: 'truck',
                    badge: self::shipmentNavBadge($user),
                );

                $items[] = self::item(
                    section: 'logistics',
                    label: 'Hospital Road Deliveries',
                    description: 'Lae AMS → Modilon by car',
                    href: getDashboardHospitalShipmentRoute('index'),
                    active: request()->routeIs('*.dashboard.hospital-shipments.*'),
                    icon: 'truck',
                );

                $openDiscrepancies = DiscrepancyReport::open()->count();

                $items[] = self::item(
                    section: 'reports',
                    label: 'Discrepancy Reports',
                    description: 'Hospital receipt issues',
                    href: getDashboardDiscrepancyRoute('index'),
                    active: request()->routeIs('*.dashboard.discrepancies.*'),
                    icon: 'clipboard',
                    badge: $openDiscrepancies > 0 ? $openDiscrepancies : null,
                );

                $items[] = self::item(
                    section: 'reports',
                    label: 'Stock Status',
                    description: 'Consumption, days of stock, suggestions',
                    href: getDashboardStockStatusRoute('index'),
                    active: request()->routeIs('*.dashboard.reports.stock-status.*'),
                    icon: 'chart',
                );

                $items[] = self::item(
                    section: 'reports',
                    label: 'Regional Reports',
                    description: 'Lae AMS warehouse summary',
                    href: getDashboardRegionalReportRoute('index'),
                    active: request()->routeIs('*.dashboard.reports.regional.*'),
                    icon: 'chart',
                );
            } elseif ($user->hasRole('pharmacy_manager')) {
                $items[] = self::item(
                    section: 'hospital',
                    label: 'Hospital Orders',
                    description: 'Request stock from Lae AMS',
                    href: getDashboardHospitalOrderRoute('index'),
                    active: request()->routeIs('*.dashboard.hospital-orders.*'),
                    icon: 'clipboard',
                );

                $items[] = self::item(
                    section: 'hospital',
                    label: 'Incoming Road Deliveries',
                    description: 'Track Lae AMS deliveries by car',
                    href: getDashboardHospitalShipmentRoute('index'),
                    active: request()->routeIs('*.dashboard.hospital-shipments.*'),
                    icon: 'truck',
                );

                $items[] = self::item(
                    section: 'hospital',
                    label: 'Discrepancy Reports',
                    description: 'Report receipt issues',
                    href: getDashboardDiscrepancyRoute('index'),
                    active: request()->routeIs('*.dashboard.discrepancies.*'),
                    icon: 'clipboard',
                );

                $items[] = self::item(
                    section: 'hospital',
                    label: 'Patients',
                    description: 'Modilon patient register',
                    href: getDashboardPatientRoute('index'),
                    active: request()->routeIs('*.dashboard.patients.*'),
                    icon: 'users',
                );

                $items[] = self::item(
                    section: 'hospital',
                    label: 'Dispensing',
                    description: 'Review dispensed medicines',
                    href: getDashboardDispensingRoute('index'),
                    active: request()->routeIs('*.dashboard.dispensing.*'),
                    icon: 'pill',
                );

                $items[] = self::item(
                    section: 'reports',
                    label: 'Stock Status',
                    description: 'Consumption & suggested requests',
                    href: getDashboardStockStatusRoute('index'),
                    active: request()->routeIs('*.dashboard.reports.stock-status.*'),
                    icon: 'chart',
                );

                $orderBadge = self::orderNavBadge($user);

                $items[] = self::item(
                    section: 'procurement',
                    label: 'Procurement Orders',
                    description: 'Track national supply status',
                    href: getDashboardOrderRoute('index'),
                    active: request()->routeIs('*.dashboard.orders.*', 'dashboard.orders.*'),
                    icon: 'clipboard',
                    badge: $orderBadge,
                );
            } elseif ($user->hasRole('pharmacist')) {
                $items[] = self::item(
                    section: 'hospital',
                    label: 'Patients',
                    description: 'Register and look up patients',
                    href: getDashboardPatientRoute('index'),
                    active: request()->routeIs('*.dashboard.patients.*'),
                    icon: 'users',
                );

                $items[] = self::item(
                    section: 'hospital',
                    label: 'Dispensing',
                    description: 'Dispense Modilon pharmacy stock',
                    href: getDashboardDispensingRoute('index'),
                    active: request()->routeIs('*.dashboard.dispensing.*'),
                    icon: 'pill',
                );

                $items[] = self::item(
                    section: 'reports',
                    label: 'Stock Status',
                    description: 'Modilon consumption & days of stock',
                    href: getDashboardStockStatusRoute('index'),
                    active: request()->routeIs('*.dashboard.reports.stock-status.*'),
                    icon: 'chart',
                );

                $orderBadge = self::orderNavBadge($user);

                $items[] = self::item(
                    section: 'procurement',
                    label: 'Procurement Orders',
                    description: 'Track national supply status',
                    href: getDashboardOrderRoute('index'),
                    active: request()->routeIs('*.dashboard.orders.*', 'dashboard.orders.*'),
                    icon: 'clipboard',
                    badge: $orderBadge,
                );
            } else {
                $orderBadge = self::orderNavBadge($user);

                $items[] = self::item(
                    section: 'procurement',
                    label: 'Procurement Orders',
                    description: self::orderNavDescription($user),
                    href: getDashboardOrderRoute('index'),
                    active: request()->routeIs('*.dashboard.orders.*', 'dashboard.orders.*'),
                    icon: 'clipboard',
                    badge: $orderBadge,
                );

                if ($user->hasAnyRole(['admin', 'procurement_officer'])) {
                    $items[] = self::item(
                        section: 'logistics',
                        label: 'Shipments to Lae AMS',
                        description: 'NDoH → Lae AMS logistics',
                        href: getDashboardTransferRoute('index'),
                        active: request()->routeIs('*.dashboard.transfers.*'),
                        icon: 'truck',
                        badge: self::shipmentNavBadge($user),
                    );

                    $items[] = self::item(
                        section: 'reports',
                        label: 'Stock Status',
                        description: 'Corridor consumption & procurement suggestions',
                        href: getDashboardStockStatusRoute('index'),
                        active: request()->routeIs('*.dashboard.reports.stock-status.*'),
                        icon: 'chart',
                    );
                }
            }
        }

        if (canManageUsers()) {
            $items[] = self::item(
                section: 'administration',
                label: 'User Management',
                description: $user->hasRole('admin')
                    ? 'Procurement, store & pharmacy managers'
                    : 'Pharmacist accounts',
                href: getDashboardUserRoute('index'),
                active: request()->routeIs('*.dashboard.users.*'),
                icon: 'users',
            );
        }

        return collect($items)
            ->groupBy('section')
            ->map(fn ($group) => $group->values()->all())
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected static function item(
        string $section,
        string $label,
        string $description,
        string $href,
        bool $active,
        string $icon,
        ?int $badge = null,
    ): array {
        return compact('section', 'label', 'description', 'href', 'active', 'icon', 'badge');
    }

    protected static function orderNavDescription($user): string
    {
        if ($user->hasRole('admin')) {
            return 'Approve & receive orders';
        }

        if ($user->hasRole('procurement_officer')) {
            return 'Create & track supplier orders';
        }

        return 'Track procurement status';
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
        if ($user->hasAnyRole(['admin', 'store_manager'])) {
            $count = StockTransfer::sent()->toLevel('lae_ams')->count();

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
