<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResolveDiscrepancyReportRequest;
use App\Http\Requests\StoreDiscrepancyReportRequest;
use App\Models\DiscrepancyReport;
use App\Models\HospitalOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscrepancyReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = DiscrepancyReport::with(['reporter', 'resolver', 'hospitalOrder', 'stockTransfer']);

        if ($user->hasRole('pharmacy_manager')) {
            $query->where('reported_by', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(15)->withQueryString();
        $openCount = $user->hasRole('store_manager') ? DiscrepancyReport::open()->count() : null;

        return view('discrepancies.index', compact('reports', 'openCount'));
    }

    public function create(Request $request): View
    {
        if (! auth()->user()->hasRole('pharmacy_manager')) {
            abort(403, 'Only Pharmacy Managers can file discrepancy reports.');
        }

        $hospitalOrder = null;
        if ($request->filled('hospital_order')) {
            $hospitalOrder = HospitalOrder::where('requested_by', auth()->id())
                ->findOrFail($request->hospital_order);
        }

        return view('discrepancies.create', compact('hospitalOrder'));
    }

    public function store(StoreDiscrepancyReportRequest $request): RedirectResponse
    {
        DiscrepancyReport::create([
            'report_number' => DiscrepancyReport::generateReportNumber(),
            'hospital_order_id' => $request->validated('hospital_order_id'),
            'stock_transfer_id' => $request->validated('stock_transfer_id'),
            'issue_type' => $request->validated('issue_type'),
            'quantity_expected' => $request->validated('quantity_expected'),
            'quantity_received' => $request->validated('quantity_received'),
            'description' => $request->validated('description'),
            'reported_by' => auth()->id(),
            'status' => 'open',
        ]);

        return redirect()
            ->to(getDashboardDiscrepancyRoute('index'))
            ->with('success', 'Discrepancy report submitted to Lae AMS.');
    }

    public function show(DiscrepancyReport $discrepancy): View
    {
        if (auth()->user()->hasRole('pharmacy_manager') && $discrepancy->reported_by !== auth()->id()) {
            abort(403, 'You can only view your own discrepancy reports.');
        }

        $discrepancy->load(['reporter', 'resolver', 'hospitalOrder', 'stockTransfer.drug']);

        return view('discrepancies.show', compact('discrepancy'));
    }

    public function resolve(ResolveDiscrepancyReportRequest $request, DiscrepancyReport $discrepancy): RedirectResponse
    {
        $discrepancy->update([
            'status' => 'resolved',
            'resolution_notes' => $request->validated('resolution_notes'),
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return redirect()
            ->to(getDashboardDiscrepancyRoute('show', $discrepancy))
            ->with('success', 'Discrepancy report marked as resolved.');
    }
}
