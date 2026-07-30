<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDispensingRecordRequest;
use App\Models\DispensingRecord;
use App\Models\Drug;
use App\Models\Patient;
use App\Services\DispensingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DispensingRecordController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizePortalAccess();

        $query = DispensingRecord::with(['patient', 'drug', 'dispenser']);

        if (auth()->user()->hasRole('pharmacist') && ! auth()->user()->hasRole('pharmacy_manager')) {
            $query->where('dispensed_by', auth()->id());
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('record_number', 'like', "%{$search}%")
                    ->orWhere('prescription_ref', 'like', "%{$search}%")
                    ->orWhere('prescriber_name', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('patient_number', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('drug', function ($dq) use ($search) {
                        $dq->where('drug_name', 'like', "%{$search}%")
                            ->orWhere('batch_number', 'like', "%{$search}%");
                    });
            });
        }

        $records = $query->latest('dispensed_at')->paginate(15)->withQueryString();

        return view('dispensing.index', compact('records'));
    }

    public function create(Request $request): View
    {
        if (! auth()->user()->hasRole('pharmacist')) {
            abort(403, 'Only pharmacists can dispense medicines.');
        }

        $patients = Patient::active()->orderBy('last_name')->orderBy('first_name')->get();
        $drugs = Drug::query()
            ->atLevel('modilon_hospital')
            ->inInventory()
            ->where('quantity_on_hand', '>', 0)
            ->where('expiry_date', '>=', now())
            ->orderBy('drug_name')
            ->orderBy('expiry_date')
            ->get();

        $selectedPatientId = $request->integer('patient_id') ?: null;

        return view('dispensing.create', compact('patients', 'drugs', 'selectedPatientId'));
    }

    public function store(StoreDispensingRecordRequest $request): RedirectResponse
    {
        $patient = Patient::findOrFail($request->validated('patient_id'));
        $data = $request->validated();
        $data['audit_date_checked'] = true;
        $data['audit_prescriber_checked'] = true;
        $data['audit_drug_dose_checked'] = true;
        $data['audit_contraindications_checked'] = true;

        try {
            $record = DispensingService::dispense($patient, auth()->user(), $data);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['drug_id' => $e->getMessage()]);
        }

        return redirect()
            ->to(getDashboardDispensingRoute('show', $record))
            ->with('success', 'Prescription audited and medicine dispensed. Stock updated.');
    }

    public function show(DispensingRecord $dispensing): View
    {
        $this->authorizePortalAccess();

        if (
            auth()->user()->hasRole('pharmacist')
            && ! auth()->user()->hasRole('pharmacy_manager')
            && $dispensing->dispensed_by !== auth()->id()
        ) {
            abort(403, 'You can only view your own dispensing records.');
        }

        $dispensing->load(['patient', 'drug', 'dispenser']);

        return view('dispensing.show', ['record' => $dispensing]);
    }

    protected function authorizePortalAccess(): void
    {
        if (! auth()->user()->hasAnyRole(['pharmacist', 'pharmacy_manager'])) {
            abort(403, 'Only Modilon pharmacy staff can access dispensing records.');
        }
    }
}
