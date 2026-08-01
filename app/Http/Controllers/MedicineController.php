<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Medicine;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function index(Request $request): View
    {
        if (! auth()->user()->hasAnyRole(['admin', 'procurement_officer'])) {
            abort(403, 'You do not have access to the medicine catalog.');
        }

        $query = Medicine::query()->with(['createdBy', 'supplier']);

        if ($request->filled('search')) {
            $query->search(trim($request->search));
        }

        if ($request->status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->status !== 'all') {
            $query->where('is_active', true);
        }

        $medicines = $query->orderBy('name')->orderBy('dosage')->paginate(15);

        return view('medicines.index', compact('medicines'));
    }

    public function create(): View
    {
        if (! auth()->user()->hasAnyRole(['admin', 'procurement_officer'])) {
            abort(403, 'You do not have access to the medicine catalog.');
        }

        $suppliers = Supplier::query()->active()->forOverseas()->orderBy('country')->orderBy('name')->get();

        return view('medicines.create', compact('suppliers'));
    }

    public function store(StoreMedicineRequest $request): RedirectResponse
    {
        $medicine = Medicine::create([
            'name' => $request->name,
            'dosage' => $request->dosage,
            'dosage_form' => $request->dosage_form,
            'unit' => $request->unit,
            'description' => $request->description,
            'supplier_id' => $request->supplier_id,
            'reorder_point' => $request->reorder_point ?? 100,
            'unit_cost' => $request->unit_cost,
            'currency' => strtoupper($request->currency),
            'is_active' => true,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        \Log::info("Medicine [{$medicine->name}] added to catalog by user ID: ".auth()->id());

        return redirect(getDashboardMedicineRoute('index'))
            ->with('success', 'Medicine added to catalog.');
    }

    public function show(Medicine $medicine): View
    {
        if (! auth()->user()->hasAnyRole(['admin', 'procurement_officer'])) {
            abort(403, 'You do not have access to the medicine catalog.');
        }

        $medicine->load(['createdBy', 'updatedBy', 'supplier']);

        return view('medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine): View
    {
        if (! auth()->user()->hasAnyRole(['admin', 'procurement_officer'])) {
            abort(403, 'You do not have access to the medicine catalog.');
        }

        $suppliers = Supplier::query()->active()->forOverseas()->orderBy('country')->orderBy('name')->get();

        return view('medicines.edit', compact('medicine', 'suppliers'));
    }

    public function update(UpdateMedicineRequest $request, Medicine $medicine): RedirectResponse
    {
        $medicine->update([
            'name' => $request->name,
            'dosage' => $request->dosage,
            'dosage_form' => $request->dosage_form,
            'unit' => $request->unit,
            'description' => $request->description,
            'supplier_id' => $request->supplier_id,
            'reorder_point' => $request->reorder_point ?? 100,
            'unit_cost' => $request->unit_cost,
            'currency' => strtoupper($request->currency),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $medicine->is_active,
            'updated_by' => auth()->id(),
        ]);

        \Log::info("Medicine [{$medicine->name}] updated by user ID: ".auth()->id());

        return redirect(getDashboardMedicineRoute('show', $medicine))
            ->with('success', 'Medicine updated successfully.');
    }

    public function deactivate(Medicine $medicine): RedirectResponse
    {
        if (! auth()->user()->hasAnyRole(['admin', 'procurement_officer'])) {
            abort(403, 'You do not have access to the medicine catalog.');
        }

        if (! $medicine->is_active) {
            return redirect()->back()->with('success', 'Medicine is already inactive.');
        }

        $medicine->update([
            'is_active' => false,
            'updated_by' => auth()->id(),
        ]);

        \Log::info("Medicine [{$medicine->name}] marked inactive by user ID: ".auth()->id());

        return redirect()->back()
            ->with('success', 'Medicine marked inactive in catalog.');
    }

    public function activate(Medicine $medicine): RedirectResponse
    {
        if (! auth()->user()->hasAnyRole(['admin', 'procurement_officer'])) {
            abort(403, 'You do not have access to the medicine catalog.');
        }

        if ($medicine->is_active) {
            return redirect()->back()->with('success', 'Medicine is already active.');
        }

        $medicine->update([
            'is_active' => true,
            'updated_by' => auth()->id(),
        ]);

        \Log::info("Medicine [{$medicine->name}] reactivated by user ID: ".auth()->id());

        return redirect()->back()
            ->with('success', 'Medicine reactivated in catalog.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only NDoH Admin can remove catalog entries.');
        }

        if ($medicine->orderItems()->exists()) {
            $medicine->update([
                'is_active' => false,
                'updated_by' => auth()->id(),
            ]);

            return redirect(getDashboardMedicineRoute('index'))
                ->with('success', 'Medicine deactivated — it is linked to existing orders.');
        }

        $name = $medicine->name;
        $medicine->delete();

        \Log::info("Medicine [{$name}] removed from catalog by user ID: ".auth()->id());

        return redirect(getDashboardMedicineRoute('index'))
            ->with('success', 'Medicine removed from catalog.');
    }
}
