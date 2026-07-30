<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizePortalAccess();

        $query = Patient::query()->withCount('dispensingRecords');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('patient_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $patients = $query->latest()->paginate(15)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create(): View
    {
        $this->authorizeWriteAccess();

        return view('patients.create');
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $patient = Patient::create([
            'patient_number' => Patient::generatePatientNumber(),
            'first_name' => $request->validated('first_name'),
            'last_name' => $request->validated('last_name'),
            'date_of_birth' => $request->validated('date_of_birth'),
            'gender' => $request->validated('gender'),
            'phone' => $request->validated('phone'),
            'facility' => $request->validated('facility') ?: 'Modilon General Hospital, Madang',
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->to(getDashboardPatientRoute('show', $patient))
            ->with('success', 'Patient registered successfully.');
    }

    public function show(Patient $patient): View
    {
        $this->authorizePortalAccess();

        $patient->load(['creator', 'dispensingRecords' => fn ($q) => $q->with(['drug', 'dispenser'])->latest()->limit(20)]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        $this->authorizeWriteAccess();

        return view('patients.edit', compact('patient'));
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $patient->update([
            'first_name' => $request->validated('first_name'),
            'last_name' => $request->validated('last_name'),
            'date_of_birth' => $request->validated('date_of_birth'),
            'gender' => $request->validated('gender'),
            'phone' => $request->validated('phone'),
            'facility' => $request->validated('facility') ?: $patient->facility,
            'is_active' => $request->boolean('is_active', $patient->is_active),
        ]);

        return redirect()
            ->to(getDashboardPatientRoute('show', $patient))
            ->with('success', 'Patient details updated.');
    }

    protected function authorizePortalAccess(): void
    {
        if (! auth()->user()->hasAnyRole(['pharmacist', 'pharmacy_manager'])) {
            abort(403, 'Only Modilon pharmacy staff can access patients.');
        }
    }

    protected function authorizeWriteAccess(): void
    {
        if (! auth()->user()->hasAnyRole(['pharmacist', 'pharmacy_manager'])) {
            abort(403, 'Only Modilon pharmacy staff can manage patients.');
        }
    }
}
