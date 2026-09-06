<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\DrugUsage;
use App\Models\Patient;
use App\Models\Ward;
use App\Models\WardStock;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class DrugUsageController extends Controller
{
    public function index(): View
    {
        $usages = DrugUsage::with(['ward', 'drug', 'patient', 'creator'])
            ->latest('usage_date')
            ->paginate(15);

        return view('drug-usages.index', compact('usages'));
    }

    public function create(): View
    {
        $wards = Ward::active()->orderBy('name')->get();
        $patients = Patient::active()->orderBy('last_name')->orderBy('first_name')->get();
        
        return view('drug-usages.create', compact('wards', 'patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'drug_id' => 'required|exists:drugs,id',
            'patient_id' => 'nullable|exists:patients,id',
            'quantity_used' => 'required|integer|min:1',
            'usage_type' => 'required|in:administration,wastage,loss,damage,expired,other',
            'usage_date' => 'required|date|before_or_equal:today',
            'recorded_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            // Check ward stock availability
            $wardStock = WardStock::where([
                'ward_id' => $validated['ward_id'],
                'drug_id' => $validated['drug_id'],
            ])->lockForUpdate()->first();

            if (!$wardStock || $wardStock->quantity_on_hand < $validated['quantity_used']) {
                $available = $wardStock ? $wardStock->quantity_on_hand : 0;
                throw ValidationException::withMessages([
                    'quantity_used' => "Insufficient ward stock. Available: {$available}",
                ]);
            }

            // Deduct from ward stock
            $wardStock->update([
                'quantity_on_hand' => $wardStock->quantity_on_hand - $validated['quantity_used'],
            ]);

            // Create usage record
            DrugUsage::create([
                'ward_id' => $validated['ward_id'],
                'drug_id' => $validated['drug_id'],
                'patient_id' => $validated['patient_id'],
                'quantity_used' => $validated['quantity_used'],
                'usage_type' => $validated['usage_type'],
                'usage_date' => $validated['usage_date'],
                'recorded_by' => $validated['recorded_by'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route(getDashboardRoutePrefix().'drug-usages.index')
            ->with('success', 'Drug usage recorded successfully. Ward stock updated.');
    }

    public function show(DrugUsage $usage): View
    {
        $usage->load(['ward', 'drug', 'patient', 'creator']);
        return view('drug-usages.show', ['usage' => $usage]);
    }

    public function destroy(DrugUsage $usage): RedirectResponse
    {
        DB::transaction(function () use ($usage) {
            // Restore ward stock
            $wardStock = WardStock::where([
                'ward_id' => $usage->ward_id,
                'drug_id' => $usage->drug_id,
            ])->lockForUpdate()->first();

            if ($wardStock) {
                $wardStock->update([
                    'quantity_on_hand' => $wardStock->quantity_on_hand + $usage->quantity_used,
                ]);
            }

            $usage->delete();
        });

        return redirect()->route(getDashboardRoutePrefix().'drug-usages.index')
            ->with('success', 'Drug usage deleted and stock restored.');
    }
}
