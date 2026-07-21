<?php

/**
 * Role-aware route helpers for dashboard-scoped Drug Inventory URLs.
 *
 * Drug inventory lives under each portal's dashboard namespace, e.g.
 * /admin/dashboard (home), /admin/drugs, /procurement-officer/orders, etc.
 */

if (! function_exists('getDashboardRoutePrefix')) {
    /**
     * Return the named-route prefix for the authenticated user's portal role.
     */
    function getDashboardRoutePrefix(): string
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return 'admin.dashboard.';
        }

        if ($user->hasRole('procurement_officer')) {
            return 'procurement-officer.dashboard.';
        }

        if ($user->hasRole('store_manager')) {
            return 'store-manager.dashboard.';
        }

        if ($user->hasRole('pharmacy_manager')) {
            return 'pharmacy-manager.dashboard.';
        }

        if ($user->hasRole('pharmacist')) {
            return 'pharmacist.dashboard.';
        }

        return 'admin.dashboard.';
    }
}

if (! function_exists('getDashboardDrugRoute')) {
    /**
     * Build a role-scoped Drug Inventory route for the current user.
     */
    function getDashboardDrugRoute(string $routeName, mixed $params = null): string
    {
        $fullRouteName = getDashboardRoutePrefix().'drugs.'.$routeName;

        return $params !== null ? route($fullRouteName, $params) : route($fullRouteName);
    }
}

if (! function_exists('getDashboardOrderRoute')) {
    /**
     * Build a role-scoped Procurement Orders route for the current user.
     * Uses the same dashboard prefix as drugs, e.g. admin.dashboard.orders.*.
     */
    function getDashboardOrderRoute(string $routeName, mixed $params = null): string
    {
        if (auth()->user()->hasAnyRole(['admin', 'procurement_officer', 'store_manager', 'pharmacy_manager', 'pharmacist'])) {
            $prefix = getDashboardRoutePrefix().'orders.';
        } else {
            $prefix = 'dashboard.orders.';
        }

        $fullRouteName = $prefix.$routeName;

        return $params !== null ? route($fullRouteName, $params) : route($fullRouteName);
    }
}

if (! function_exists('getDashboardTransferRoute')) {
    /**
     * Build a role-scoped Stock Transfer route for the current user.
     */
    function getDashboardTransferRoute(string $routeName, mixed $params = null): string
    {
        if (auth()->user()->hasAnyRole(['admin', 'procurement_officer', 'store_manager'])) {
            $prefix = getDashboardRoutePrefix().'transfers.';
        } else {
            abort(403, 'You do not have access to shipments.');
        }

        $fullRouteName = $prefix.$routeName;

        return $params !== null ? route($fullRouteName, $params) : route($fullRouteName);
    }
}

if (! function_exists('canManageTransfers')) {
    /**
     * Whether the current user can record shipments to Lae AMS.
     */
    function canManageTransfers(): bool
    {
        return auth()->user()->hasRole('procurement_officer');
    }
}

if (! function_exists('canManageOrders')) {
    /**
     * Whether the current user can create/edit procurement orders.
     */
    function canManageOrders(): bool
    {
        return auth()->user()->hasRole('procurement_officer');
    }
}

if (! function_exists('canApproveOrders')) {
    /**
     * Whether the current user can approve and receive orders.
     */
    function canApproveOrders(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}

if (! function_exists('canReceiveTransfers')) {
    /**
     * Whether the current user can confirm receipt at Lae AMS.
     */
    function canReceiveTransfers(): bool
    {
        return auth()->user()->hasRole('store_manager');
    }
}

if (! function_exists('getRoleDashboardRoute')) {
    /**
     * Return the main dashboard route for the authenticated user's portal role.
     */
    function getRoleDashboardRoute(): string
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return route('dashboard.admin');
        }

        if ($user->hasRole('procurement_officer')) {
            return route('dashboard.procurement_officer');
        }

        if ($user->hasRole('store_manager')) {
            return route('dashboard.store_manager');
        }

        if ($user->hasRole('pharmacy_manager')) {
            return route('dashboard.pharmacy_manager');
        }

        if ($user->hasRole('pharmacist')) {
            return route('dashboard.pharmacist');
        }

        return route('dashboard');
    }
}
