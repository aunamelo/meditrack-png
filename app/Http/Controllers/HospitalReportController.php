<?php

namespace App\Http\Controllers;

use App\Services\HospitalReportService;
use App\Services\ReportCsvService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HospitalReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizePharmacyManager();

        [$from, $to] = $this->period($request);
        $report = HospitalReportService::generate(auth()->id(), $from, $to);

        return view('reports.hospital', compact('report', 'from', 'to'));
    }

    public function print(Request $request): View
    {
        $this->authorizePharmacyManager();

        [$from, $to] = $this->period($request);
        $report = HospitalReportService::generate(auth()->id(), $from, $to);

        return view('reports.print.hospital', compact('report', 'from', 'to'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizePharmacyManager();

        [$from, $to] = $this->period($request);
        $report = HospitalReportService::generate(auth()->id(), $from, $to);
        $filename = 'modilon-hospital-report-'.$from->toDateString().'-to-'.$to->toDateString().'.csv';

        return ReportCsvService::download($filename, HospitalReportService::toCsvRows($report));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function period(Request $request): array
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    private function authorizePharmacyManager(): void
    {
        if (! auth()->user()->hasRole('pharmacy_manager')) {
            abort(403, 'Only Pharmacy Managers can generate hospital reports.');
        }
    }
}
