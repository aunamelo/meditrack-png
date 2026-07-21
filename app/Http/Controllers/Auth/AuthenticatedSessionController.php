<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Order;
use App\Models\StockTransfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private const ROLE_SLUG_MAP = [
        'admin'                => 'admin',
        'pharmacist'            => 'pharmacist',
        'pharmacy-manager'      => 'pharmacy_manager',
        'procurement-officer'   => 'procurement_officer',
        'store-manager'         => 'store_manager',
    ];

    private const ROLE_ROUTE_MAP = [
        'admin'                => 'dashboard.admin',
        'pharmacist'            => 'dashboard.pharmacist',
        'pharmacy_manager'      => 'dashboard.pharmacy_manager',
        'procurement_officer'   => 'dashboard.procurement_officer',
        'store_manager'         => 'dashboard.store_manager',
    ];

    private const ROLE_LABEL_MAP = [
        'admin'                => 'NDoH Admin',
        'pharmacist'            => 'Pharmacist',
        'pharmacy_manager'      => 'Pharmacy Manager',
        'procurement_officer'   => 'Procurement Officer',
        'store_manager'         => 'Store Manager',
    ];

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $portalSlug = $request->input('role');
        $expectedSpatieRole = self::ROLE_SLUG_MAP[$portalSlug] ?? null;

        if ($expectedSpatieRole !== null && ! $user->hasRole($expectedSpatieRole)) {
            $attemptedPortalLabel = self::ROLE_LABEL_MAP[$expectedSpatieRole] ?? 'this';

            $actualRoleKey = null;
            foreach (self::ROLE_ROUTE_MAP as $spatieRole => $route) {
                if ($user->hasRole($spatieRole)) {
                    $actualRoleKey = $spatieRole;
                    break;
                }
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

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

        $routeName = 'dashboard';
        foreach (self::ROLE_ROUTE_MAP as $spatieRole => $route) {
            if ($user->hasRole($spatieRole)) {
                $routeName = $route;
                break;
            }
        }

        // Shown as a fading toast on the dashboard right after redirect.
        $request->session()->flash('login_success', 'Login successful!');

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

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}