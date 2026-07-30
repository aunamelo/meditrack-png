<?php

namespace App\Http\Controllers;

use App\Services\HospitalReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalReportController extends Controller
{
    public function index(Request $request): View
    {
        if (! auth()->user()->hasRole('pharmacy_manager')) {
            abort(403, 'Only Pharmacy Managers can generate hospital reports.');
        }

        $from = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfDay();

        $report = HospitalReportService::generate(auth()->id(), $from, $to);

        return view('reports.hospital', compact('report', 'from', 'to'));
    }
}
