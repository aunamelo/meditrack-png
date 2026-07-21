<?php

namespace App\Services;

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
        }

        if ($user->hasAnyRole(['admin', 'procurement_officer', 'store_manager'])) {
            $items[] = self::item(
                section: 'logistics',
                label: 'Lae AMS Shipments',
                description: 'NDoH → Lae warehouse transfers',
                href: getDashboardTransferRoute('index'),
                active: request()->routeIs('*.dashboard.transfers.*'),
                icon: 'truck',
                badge: self::shipmentNavBadge($user),
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
}
