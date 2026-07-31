<?php

namespace App\Http\Controllers;

use App\Services\NdohReportService;
use App\Services\ReportCsvService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NdohReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        [$from, $to] = $this->period($request);
        $report = NdohReportService::generate($from, $to);

        return view('reports.ndoh', compact('report', 'from', 'to'));
    }

    public function print(Request $request): View
    {
        $this->authorizeAdmin();

        [$from, $to] = $this->period($request);
        $report = NdohReportService::generate($from, $to);

        return view('reports.print.ndoh', compact('report', 'from', 'to'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        [$from, $to] = $this->period($request);
        $report = NdohReportService::generate($from, $to);
        $filename = 'ndoh-national-report-'.$from->toDateString().'-to-'.$to->toDateString().'.csv';

        return ReportCsvService::download($filename, NdohReportService::toCsvRows($report));
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

    private function authorizeAdmin(): void
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only NDoH Admin can generate national reports.');
        }
    }
}
