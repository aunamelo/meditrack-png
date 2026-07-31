<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PortalLoginService
{
    public const ROLE_SLUG_MAP = [
        'admin' => 'admin',
        'pharmacist' => 'pharmacist',
        'pharmacy-manager' => 'pharmacy_manager',
        'procurement-officer' => 'procurement_officer',
        'store-manager' => 'store_manager',
    ];

    public const ROLE_ROUTE_MAP = [
        'admin' => 'dashboard.admin',
        'pharmacist' => 'dashboard.pharmacist',
        'pharmacy_manager' => 'dashboard.pharmacy_manager',
        'procurement_officer' => 'dashboard.procurement_officer',
        'store_manager' => 'dashboard.store_manager',
    ];

    public const ROLE_LABEL_MAP = [
        'admin' => 'NDoH Admin',
        'pharmacist' => 'Pharmacist',
        'pharmacy_manager' => 'Pharmacy Manager',
        'procurement_officer' => 'Procurement Officer',
        'store_manager' => 'Store Manager',
    ];

    public static function microsoftConfigured(): bool
    {
        return filled(config('services.microsoft.client_id'))
            && filled(config('services.microsoft.client_secret'))
            && filled(config('services.microsoft.redirect'));
    }

    /**
     * Ensure the authenticated user matches the selected portal role (if any).
     *
     * @throws ValidationException
     */
    public static function assertPortalRole(User $user, ?string $portalSlug): void
    {
        $expectedSpatieRole = self::ROLE_SLUG_MAP[$portalSlug] ?? null;

        if ($expectedSpatieRole === null || $user->hasRole($expectedSpatieRole)) {
            return;
        }

        $attemptedPortalLabel = self::ROLE_LABEL_MAP[$expectedSpatieRole] ?? 'this';

        $actualRoleKey = null;
        foreach (self::ROLE_ROUTE_MAP as $spatieRole => $route) {
            if ($user->hasRole($spatieRole)) {
                $actualRoleKey = $spatieRole;
                break;
            }
        }

        Auth::guard('web')->logout();

        $message = $actualRoleKey
            ? sprintf(
                'This account is not registered as %s. Please log in through the %s portal instead.',
                $attemptedPortalLabel,
                self::ROLE_LABEL_MAP[$actualRoleKey]
            )
            : sprintf(
                'This account is not registered as %s.',
                $attemptedPortalLabel
            );

        throw ValidationException::withMessages([
            'email' => $message,
        ]);
    }

    public static function completeLogin(Request $request, User $user): RedirectResponse
    {
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $routeName = 'dashboard';
        foreach (self::ROLE_ROUTE_MAP as $spatieRole => $route) {
            if ($user->hasRole($spatieRole)) {
                $routeName = $route;
                break;
            }
        }

        $roleMeta = null;
        foreach (array_keys(self::ROLE_ROUTE_MAP) as $spatieRole) {
            if ($user->hasRole($spatieRole)) {
                $roleMeta = config("portal.roles.{$spatieRole}");
                break;
            }
        }

        $request->session()->flash(
            'login_success',
            $roleMeta
                ? 'Welcome to your '.$roleMeta['label'].' workspace'
                : 'Welcome to your MediTrack workspace'
        );

        if ($user->hasRole('admin')) {
            $pendingOrderCount = Order::pending()->count();
            if ($pendingOrderCount > 0) {
                $request->session()->flash('admin_pending_orders', $pendingOrderCount);
            }
        }

        if ($user->hasRole('store_manager')) {
            $pendingShipmentCount = StockTransfer::sent()->toLevel('lae_ams')->count();
            if ($pendingShipmentCount > 0) {
                $request->session()->flash('store_pending_shipments', $pendingShipmentCount);
            }
        }

        return redirect()->route($routeName);
    }
}
