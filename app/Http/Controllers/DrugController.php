<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDrugRequest;
use App\Http\Requests\UpdateDrugRequest;
use App\Models\Drug;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DrugController extends Controller
{
    /**
     * Display a listing of drugs based on user role.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Drug::query();

        // Hide written-off drugs from the default list (admin can filter to view them)
        if ($request->status !== 'written_off') {
            $query->inInventory();
        }

        // Role-based filtering
        if ($user->hasRole('procurement_officer')) {
            $query->atLevel('ndoh');
        } elseif ($user->hasRole('store_manager')) {
            $query->atLevel('lae_ams');
        } elseif ($user->hasRole('pharmacy_manager')) {
            $query->atLevel('modilon_hospital');
        } elseif ($user->hasRole('pharmacist')) {
            $query->atLevel('modilon_hospital');
        }
        // NDoH Admin sees all levels (no filter applied)

        // Search by drug name or batch number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->byDrugName($search)
                  ->orWhereByBatch($search);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'expiring_soon':
                    $query->expiring();
                    break;
                case 'expired':
                    $query->expired();
                    break;
                case 'low_stock':
                    $query->lowStock();
                    break;
                case 'written_off':
                    $query->where('status', 'written_off');
                    break;
            }
        }

        // Filter by level (only for admin)
        if ($user->hasRole('admin') && $request->filled('level')) {
            $query->atLevel($request->level);
        }

        // Filter by expiry date range
        if ($request->filled('expiry_from')) {
            $query->where('expiry_date', '>=', $request->expiry_from);
        }
        if ($request->filled('expiry_to')) {
            $query->where('expiry_date', '<=', $request->expiry_to);
        }

        // Sorting
        $sortField = $request->get('sort', 'expiry_date');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $drugs = $query->paginate(15);

        return view('drugs.index', compact('drugs'));
    }

    /**
     * Show the form for creating a new drug.
     * Only accessible to Procurement Officer.
     */
    public function create(): View
    {
        if (!auth()->user()->hasRole('procurement_officer')) {
            abort(403, 'Only Procurement Officers can create drug entries.');
        }

        return view('drugs.create');
    }

    /**
     * Store a newly created drug in storage.
     */
    public function store(StoreDrugRequest $request)
    {
        $drug = Drug::create([
            'drug_name' => $request->drug_name,
            'description' => $request->description,
            'dosage' => $request->dosage,
            'dosage_form' => $request->dosage_form,
            'batch_number' => $request->batch_number,
            'expiry_date' => $request->expiry_date,
            'quantity_received' => $request->quantity_received,
            'quantity_on_hand' => $request->quantity_received, // Initial quantity equals received
            'reorder_point' => $request->reorder_point ?? 100,
            'unit' => $request->unit,
            'supplier' => $request->supplier,
            'cost_per_unit' => $request->cost_per_unit,
            'storage_location' => $request->storage_location,
            'level' => 'ndoh', // Only Procurement Officer creates at NDoH level
            'status' => 'active',
            'received_date' => now(),
            'notes' => $request->notes,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        // Log to audit trail (you can implement a proper audit log system)
        \Log::info("Drug [{$drug->drug_name}] added by user ID: " . auth()->id());

        return redirect()->route($this->getDashboardPrefix().'drugs.index')
            ->with('success', 'Drug entered successfully.');
    }

    /**
     * Display the specified drug details.
     */
    public function show(Drug $drug): View
    {
        $user = auth()->user();

        // Role-based access control
        if ($user->hasRole('procurement_officer') && $drug->level !== 'ndoh') {
            abort(403, 'You can only view NDoH level drugs.');
        }
        if ($user->hasRole('store_manager') && $drug->level !== 'lae_ams') {
            abort(403, 'You can only view Lae AMS level drugs.');
        }
        if ($user->hasRole('pharmacy_manager') && $drug->level !== 'modilon_hospital') {
            abort(403, 'You can only view Modilon Hospital level drugs.');
        }
        if ($user->hasRole('pharmacist') && $drug->level !== 'modilon_hospital') {
            abort(403, 'You can only view Modilon Hospital level drugs.');
        }
        // NDoH Admin can view all levels

        return view('drugs.show', compact('drug'));
    }

    /**
     * Show the form for editing the specified drug.
     * Limited fields can be edited.
     */
    public function edit(Drug $drug): View
    {
        $user = auth()->user();

        // Role-based access control for editing
        if ($user->hasRole('procurement_officer')) {
            if ($drug->level !== 'ndoh') {
                abort(403, 'You can only edit NDoH level drugs.');
            }
            // Check if drug has been transferred (you may add a transfer status check)
        } elseif ($user->hasRole('pharmacy_manager')) {
            if ($drug->level !== 'modilon_hospital') {
                abort(403, 'You can only edit hospital level drugs.');
            }
        } elseif ($user->hasRole('admin')) {
            // Admin can edit all drugs
        } else {
            abort(403, 'You do not have permission to edit this drug.');
        }

        return view('drugs.edit', compact('drug'));
    }

    /**
     * Update the specified drug in storage.
     * Only limited fields can be updated.
     */
    public function update(UpdateDrugRequest $request, Drug $drug)
    {

        // Track changes for audit log
        $changes = [];
        if ($drug->notes != $request->notes) {
            $changes[] = 'notes';
        }
        if ($drug->reorder_point != $request->reorder_point) {
            $changes[] = 'reorder_point';
        }
        if ($drug->storage_location != $request->storage_location) {
            $changes[] = 'storage_location';
        }

        $drug->update([
            'notes' => $request->notes,
            'reorder_point' => $request->reorder_point,
            'storage_location' => $request->storage_location,
            'updated_by' => auth()->id(),
        ]);

        // Log to audit trail
        \Log::info("Drug [{$drug->drug_name}] updated by user ID: " . auth()->id() . " - changed: " . implode(', ', $changes));

        return redirect()->route($this->getDashboardPrefix().'drugs.show', $drug->id)
            ->with('success', 'Drug updated successfully.');
    }

    /**
     * Remove the specified drug from storage.
     * Only accessible to NDoH Admin.
     */
    public function destroy(Drug $drug)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only NDoH Admin can delete drugs.');
        }

        $drugName = $drug->drug_name;

        // Mark as written off — removed from inventory but kept for audit trail
        $drug->update([
            'status' => 'written_off',
            'quantity_on_hand' => 0,
            'updated_by' => auth()->id(),
        ]);

        \Log::info("Drug [{$drugName}] removed from inventory (written off) by user ID: " . auth()->id());

        return redirect()->route($this->getDashboardPrefix().'drugs.index')
            ->with('success', 'Drug removed from inventory.');
    }

    /**
     * Return the named-route prefix for the authenticated user's portal role.
     * Used for role-aware redirects after create/update/delete actions.
     */
    private function getDashboardPrefix(): string
    {
        return getDashboardRoutePrefix();
    }

    /**
     * Search for a drug by batch number (API endpoint).
     */
    public function searchByBatch(string $batch_number): JsonResponse
    {
        $drug = Drug::byBatch($batch_number)->first();

        if (!$drug) {
            return response()->json(['message' => 'Drug not found'], 404);
        }

        return response()->json([
            'drug_name' => $drug->drug_name,
            'batch_number' => $drug->batch_number,
            'expiry_date' => $drug->expiry_date->format('Y-m-d'),
            'quantity_on_hand' => $drug->quantity_on_hand,
            'status' => $drug->status,
            'level' => $drug->level,
        ]);
    }

    /**
     * Get batch details for Receiving/Dispensing modules (API endpoint).
     */
    public function getBatchDetails(string $batch_number, string $level): JsonResponse
    {
        $drug = Drug::byBatch($batch_number)->atLevel($level)->first();

        if (!$drug) {
            return response()->json(['message' => 'Drug not found at this level'], 404);
        }

        return response()->json([
            'id' => $drug->id,
            'drug_name' => $drug->drug_name,
            'batch_number' => $drug->batch_number,
            'expiry_date' => $drug->expiry_date->format('Y-m-d'),
            'quantity_on_hand' => $drug->quantity_on_hand,
            'can_dispense' => $drug->canBeDispensed(),
            'status' => $drug->status,
        ]);
    }
}
