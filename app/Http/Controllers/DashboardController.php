<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Role-aware dashboard with stats, alerts, and module shortcuts.
     */
    public function index(): View
    {
        return view('dashboard.index', DashboardService::forUser(auth()->user()));
    }
}
