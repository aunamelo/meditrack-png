<?php

namespace App\Http\Controllers;

use App\Services\RegionalReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegionalReportController extends Controller
{
    public function index(Request $request): View
    {
        if (! auth()->user()->hasRole('store_manager')) {
            abort(403, 'Only Store Managers can generate regional reports.');
        }

        $from = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfDay();

        $report = RegionalReportService::generate($from, $to);

        return view('reports.regional', compact('report', 'from', 'to'));
    }
}
