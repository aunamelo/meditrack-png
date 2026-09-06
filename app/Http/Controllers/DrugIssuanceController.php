<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\DrugIssuance;
use App\Models\Ward;
use App\Models\WardStock;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class DrugIssuanceController extends Controller
{
    public function index(): View
    {
        $issuances = DrugIssuance::with(['ward', 'drug', 'issuer', 'receiver'])
            ->latest('issuance_date')
            ->paginate(15);

        return view('drug-issuances.index', compact('issuances'));
    }

    public function create(): View
    {
        $wards = Ward::active()->orderBy('name')->get();
        $drugs = Drug::query()
            ->atLevel('modilon_hospital')
            ->inInventory()
            ->where('quantity_on_hand', '>', 0)
            ->where('expiry_date', '>=', now())
            ->orderBy('drug_name')
            ->get();

        return view('drug-issuances.create', compact('wards', 'drugs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'drug_id' => 'required|exists:drugs,id',
            'quantity_issued' => 'required|integer|min:1',
            'issuance_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            $drug = Drug::query()->lockForUpdate()->findOrFail($validated['drug_id']);
            $quantityIssued = (int) $validated['quantity_issued'];

            if ($drug->quantity_on_hand < $quantityIssued) {
                throw ValidationException::withMessages([
                    'quantity_issued' => "Insufficient stock. Available: {$drug->quantity_on_hand} {$drug->unit}",
                ]);
            }

            // Deduct from pharmacy stock
            $drug->update([
                'quantity_on_hand' => $drug->quantity_on_hand - $quantityIssued,
                'last_issued_date' => now(),
            ]);

            // Create issuance record
            DrugIssuance::create([
                'issuance_number' => DrugIssuance::generateIssuanceNumber(),
                'ward_id' => $validated['ward_id'],
                'drug_id' => $validated['drug_id'],
                'quantity_issued' => $quantityIssued,
                'issuance_date' => $validated['issuance_date'],
                'batch_number' => $drug->batch_number,
                'strength' => $drug->dosage,
                'dosage_form' => $drug->dosage_form,
                'notes' => $validated['notes'],
                'issued_by' => auth()->id(),
            ]);

            // Update or create ward stock
            $wardStock = WardStock::firstOrCreate(
                ['ward_id' => $validated['ward_id'], 'drug_id' => $validated['drug_id']],
                ['quantity_on_hand' => 0, 'reorder_point' => 10]
            );

            $wardStock->update([
                'quantity_on_hand' => $wardStock->quantity_on_hand + $quantityIssued,
                'last_issued_at' => now(),
            ]);
        });

        return redirect()->route(getDashboardRoutePrefix().'drug-issuances.index')
            ->with('success', 'Drug issued to ward successfully. Pharmacy stock updated.');
    }

    public function show(DrugIssuance $issuance): View
    {
        $issuance->load(['ward', 'drug', 'issuer', 'receiver']);
        return view('drug-issuances.show', ['issuance' => $issuance]);
    }

    public function receive(Request $request, DrugIssuance $issuance): RedirectResponse
    {
        if ($issuance->isReceived()) {
            return back()->with('error', 'This issuance has already been received.');
        }

        $issuance->update([
            'received_by' => auth()->id(),
            'received_at' => now(),
        ]);

        return back()->with('success', 'Drug issuance received successfully.');
    }

    public function destroy(DrugIssuance $issuance): RedirectResponse
    {
        if ($issuance->isReceived()) {
            return back()->with('error', 'Cannot delete a received issuance.');
        }

        DB::transaction(function () use ($issuance) {
            // Restore pharmacy stock
            $drug = Drug::query()->lockForUpdate()->findOrFail($issuance->drug_id);
            $drug->update([
                'quantity_on_hand' => $drug->quantity_on_hand + $issuance->quantity_issued,
            ]);

            // Deduct from ward stock
            $wardStock = WardStock::where([
                'ward_id' => $issuance->ward_id,
                'drug_id' => $issuance->drug_id,
            ])->first();

            if ($wardStock) {
                $wardStock->update([
                    'quantity_on_hand' => max(0, $wardStock->quantity_on_hand - $issuance->quantity_issued),
                ]);
            }

            $issuance->delete();
        });

        return redirect()->route(getDashboardRoutePrefix().'drug-issuances.index')
            ->with('success', 'Drug issuance deleted and stock restored.');
    }
}
