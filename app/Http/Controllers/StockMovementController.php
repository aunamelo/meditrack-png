<?php

namespace App\Http\Controllers;

use App\Services\PortalNavigationService;
use App\Services\StockMovementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        if (! $user->hasAnyRole([
            'admin',
            'procurement_officer',
            'store_manager',
            'pharmacy_manager',
            'pharmacist',
        ])) {
            abort(403, 'You do not have access to stock movements.');
        }

        $meta = PortalNavigationService::currentRoleMeta();
        $level = $meta['inventory_level'] ?? 'modilon_hospital';

        $from = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfDay();

        $search = $request->filled('search') ? trim($request->search) : null;
        $movements = StockMovementService::forLevel($level, $from, $to, $search);

        $levelLabels = [
            'ndoh' => 'NDoH central stock',
            'lae_ams' => 'Lae AMS warehouse',
            'modilon_hospital' => 'Modilon General Hospital',
        ];

        return view('reports.stock-movements', [
            'movements' => $movements,
            'level' => $level,
            'levelLabel' => $levelLabels[$level] ?? $level,
            'from' => $from,
            'to' => $to,
            'search' => $search,
        ]);
    }
}
