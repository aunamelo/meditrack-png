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

if (! function_exists('getDashboardMedicineRoute')) {
    /**
     * Build a role-scoped Medicine Catalog route for the current user.
     */
    function getDashboardMedicineRoute(string $routeName, mixed $params = null): string
    {
        if (! auth()->user()->hasAnyRole(['admin', 'procurement_officer'])) {
            abort(403, 'You do not have access to the medicine catalog.');
        }

        $fullRouteName = getDashboardRoutePrefix().'medicines.'.$routeName;

        return $params !== null ? route($fullRouteName, $params) : route($fullRouteName);
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
     */
    function getDashboardOrderRoute(string $routeName, mixed $params = null): string
    {
        if (auth()->user()->hasRole('store_manager')) {
            abort(403, 'Store Managers cannot access national procurement orders.');
        }

        if (auth()->user()->hasAnyRole(['admin', 'procurement_officer', 'pharmacy_manager', 'pharmacist'])) {
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
            abort(403, 'You do not have access to NDoH shipments.');
        }

        $fullRouteName = $prefix.$routeName;

        return $params !== null ? route($fullRouteName, $params) : route($fullRouteName);
    }
}

if (! function_exists('canManageTransfers')) {
    /**
     * Whether the current user can ship stock from NDoH to Lae AMS.
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

if (! function_exists('getDashboardUserRoute')) {
    /**
     * Build a role-scoped User Management route for the current user.
     */
    function getDashboardUserRoute(string $routeName, mixed $params = null): string
    {
        if (! auth()->user()->hasAnyRole(['admin', 'pharmacy_manager'])) {
            abort(403, 'You do not have access to user management.');
        }

        $fullRouteName = getDashboardRoutePrefix().'users.'.$routeName;

        return $params !== null ? route($fullRouteName, $params) : route($fullRouteName);
    }
}

if (! function_exists('canManageUsers')) {
    /**
     * Whether the current user can create, edit, or delete portal user accounts.
     */
    function canManageUsers(): bool
    {
        return \App\Services\UserManagementService::canManageUsers(auth()->user());
    }
}

if (! function_exists('getDashboardHospitalOrderRoute')) {
    function getDashboardHospitalOrderRoute(string $routeName, mixed $params = null): string
    {
        if (! auth()->user()->hasAnyRole(['store_manager', 'pharmacy_manager'])) {
            abort(403, 'You do not have access to hospital orders.');
        }

        $fullRouteName = getDashboardRoutePrefix().'hospital-orders.'.$routeName;

        return $params !== null ? route($fullRouteName, $params) : route($fullRouteName);
    }
}

if (! function_exists('getDashboardHospitalShipmentRoute')) {
    function getDashboardHospitalShipmentRoute(string $routeName, mixed $params = null): string
    {
        if (! auth()->user()->hasAnyRole(['store_manager', 'pharmacy_manager'])) {
            abort(403, 'You do not have access to hospital road deliveries.');
        }

        $fullRouteName = getDashboardRoutePrefix().'hospital-shipments.'.$routeName;

        return $params !== null ? route($fullRouteName, $params) : route($fullRouteName);
    }
}

if (! function_exists('getDashboardDiscrepancyRoute')) {
    function getDashboardDiscrepancyRoute(string $routeName, mixed $params = null): string
    {
        if (! auth()->user()->hasAnyRole(['store_manager', 'pharmacy_manager'])) {
            abort(403, 'You do not have access to discrepancy reports.');
        }

        $fullRouteName = getDashboardRoutePrefix().'discrepancies.'.$routeName;

        return $params !== null ? route($fullRouteName, $params) : route($fullRouteName);
    }
}

if (! function_exists('getDashboardRegionalReportRoute')) {
    function getDashboardRegionalReportRoute(string $routeName = 'index', mixed $params = null): string
    {
        if (! auth()->user()->hasRole('store_manager')) {
            abort(403, 'You do not have access to regional reports.');
        }

        $fullRouteName = getDashboardRoutePrefix().'reports.regional.'.$routeName;

        return $params !== null ? route($fullRouteName, $params) : route($fullRouteName);
    }
}

if (! function_exists('canManageHospitalOrders')) {
    function canManageHospitalOrders(): bool
    {
        return auth()->user()->hasRole('store_manager');
    }
}

if (! function_exists('canRequestHospitalOrders')) {
    function canRequestHospitalOrders(): bool
    {
        return auth()->user()->hasRole('pharmacy_manager');
    }
}

if (! function_exists('ndohToLaeAmsTransferStatusLabel')) {
    /**
     * Status labels for Department of Health (NDoH) shipments to Lae AMS.
     */
    function ndohToLaeAmsTransferStatusLabel(string $status): string
    {
        return match ($status) {
            'sent' => 'Shipped to Lae AMS',
            'received' => 'Received at Lae AMS',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}

if (! function_exists('logisticsTransferStatusLabel')) {
    /**
     * Status labels for Lae AMS → hospital road deliveries.
     */
    function logisticsTransferStatusLabel(string $status): string
    {
        return match ($status) {
            'sent' => 'In transit by road',
            'received' => 'Received',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}

if (! function_exists('hospitalOrderStatusLabel')) {
    function hospitalOrderStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'shipped' => 'In transit by road',
            'received' => 'Received',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
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
