<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\DrugReturn;
use App\Models\Ward;
use App\Models\WardStock;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class DrugReturnController extends Controller
{
    public function index(): View
    {
        $returns = DrugReturn::with(['ward', 'drug', 'returner', 'receiver'])
            ->latest('return_date')
            ->paginate(15);

        return view('drug-returns.index', compact('returns'));
    }

    public function create(): View
    {
        $wards = Ward::active()->orderBy('name')->get();
        
        return view('drug-returns.create', compact('wards'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ward_id' => 'required|exists:wards,id',
            'drug_id' => 'required|exists:drugs,id',
            'quantity_returned' => 'required|integer|min:1',
            'return_date' => 'required|date|before_or_equal:today',
            'return_reason' => 'required|in:excess,expired,damaged,wrong_batch,patient_discharge,other',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            // Check ward stock availability
            $wardStock = WardStock::where([
                'ward_id' => $validated['ward_id'],
                'drug_id' => $validated['drug_id'],
            ])->lockForUpdate()->first();

            if (!$wardStock || $wardStock->quantity_on_hand < $validated['quantity_returned']) {
                $available = $wardStock ? $wardStock->quantity_on_hand : 0;
                throw ValidationException::withMessages([
                    'quantity_returned' => "Insufficient ward stock. Available: {$available}",
                ]);
            }

            $drug = Drug::findOrFail($validated['drug_id']);

            // Deduct from ward stock
            $wardStock->update([
                'quantity_on_hand' => $wardStock->quantity_on_hand - $validated['quantity_returned'],
            ]);

            // Create return record
            DrugReturn::create([
                'return_number' => DrugReturn::generateReturnNumber(),
                'ward_id' => $validated['ward_id'],
                'drug_id' => $validated['drug_id'],
                'quantity_returned' => $validated['quantity_returned'],
                'return_date' => $validated['return_date'],
                'return_reason' => $validated['return_reason'],
                'batch_number' => $drug->batch_number,
                'notes' => $validated['notes'],
                'returned_by' => auth()->id(),
            ]);
        });

        return redirect()->route(getDashboardRoutePrefix().'drug-returns.index')
            ->with('success', 'Drug return recorded successfully. Ward stock updated.');
    }

    public function show(DrugReturn $return): View
    {
        $return->load(['ward', 'drug', 'returner', 'receiver']);
        return view('drug-returns.show', ['return' => $return]);
    }

    public function receive(Request $request, DrugReturn $return): RedirectResponse
    {
        if ($return->isReceived()) {
            return back()->with('error', 'This return has already been received.');
        }

        DB::transaction(function () use ($return) {
            // Restore to pharmacy stock
            $drug = Drug::query()->lockForUpdate()->findOrFail($return->drug_id);
            $drug->update([
                'quantity_on_hand' => $drug->quantity_on_hand + $return->quantity_returned,
            ]);

            $return->update([
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);
        });

        return back()->with('success', 'Drug return received and pharmacy stock restored.');
    }

    public function destroy(DrugReturn $return): RedirectResponse
    {
        if ($return->isReceived()) {
            return back()->with('error', 'Cannot delete a received return.');
        }

        DB::transaction(function () use ($return) {
            // Restore ward stock
            $wardStock = WardStock::where([
                'ward_id' => $return->ward_id,
                'drug_id' => $return->drug_id,
            ])->lockForUpdate()->first();

            if ($wardStock) {
                $wardStock->update([
                    'quantity_on_hand' => $wardStock->quantity_on_hand + $return->quantity_returned,
                ]);
            }

            $return->delete();
        });

        return redirect()->route(getDashboardRoutePrefix().'drug-returns.index')
            ->with('success', 'Drug return deleted and stock restored.');
    }
}
