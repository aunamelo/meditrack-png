<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveHospitalOrderRequest;
use App\Http\Requests\RejectHospitalOrderRequest;
use App\Http\Requests\ShipHospitalOrderRequest;
use App\Http\Requests\StoreHospitalOrderRequest;
use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\Vehicle;
use App\Services\HospitalShipmentService;
use App\Services\LmisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalOrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = HospitalOrder::with(['requester', 'reviewer', 'sourceDrug', 'stockTransfer']);

        if ($user->hasRole('pharmacy_manager')) {
            $query->where('requested_by', $user->id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('drug_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $pendingCount = $user->hasRole('store_manager')
            ? HospitalOrder::pending()->count()
            : null;

        return view('hospital-orders.index', compact('orders', 'pendingCount'));
    }

    public function create(): View
    {
        if (! auth()->user()->hasRole('pharmacy_manager')) {
            abort(403, 'Only Pharmacy Managers can request stock from Lae AMS.');
        }

        $stockOptions = LmisService::hospitalRequisitionOptions();

        return view('hospital-orders.create', compact('stockOptions'));
    }

    public function store(StoreHospitalOrderRequest $request): RedirectResponse
    {
        HospitalOrder::create([
            'order_number' => HospitalOrder::generateOrderNumber(),
            'drug_name' => $request->validated('drug_name'),
            'dosage' => $request->validated('dosage'),
            'quantity_requested' => $request->validated('quantity_requested'),
            'notes' => $request->validated('notes'),
            'requested_by' => auth()->id(),
            'status' => 'pending',
        ]);

        return redirect()
            ->to(getDashboardHospitalOrderRoute('index'))
            ->with('success', 'Hospital order submitted to Lae AMS.');
    }

    public function show(HospitalOrder $hospitalOrder): View
    {
        $user = auth()->user();

        if ($user->hasRole('pharmacy_manager') && $hospitalOrder->requested_by !== $user->id) {
            abort(403, 'You can only view your own hospital orders.');
        }

        $hospitalOrder->load(['requester', 'reviewer', 'sourceDrug', 'stockTransfer.drug', 'stockTransfer.destinationDrug', 'stockTransfer.vehicle', 'discrepancyReports']);

        $availableDrugs = collect();
        $vehicles = collect();
        if ($user->hasRole('store_manager') && $hospitalOrder->canApprove()) {
            $availableDrugs = Drug::query()
                ->inInventory()
                ->atLevel('lae_ams')
                ->where('quantity_on_hand', '>', 0)
                ->where('expiry_date', '>=', now())
                ->where('drug_name', 'like', '%'.$hospitalOrder->drug_name.'%')
                ->orderBy('drug_name')
                ->get();
        }

        if ($user->hasRole('store_manager') && $hospitalOrder->canShip()) {
            $vehicles = Vehicle::query()->active()->atDepot('lae_ams')->orderBy('name')->get();
        }

        return view('hospital-orders.show', compact('hospitalOrder', 'availableDrugs', 'vehicles'));
    }

    public function approve(ApproveHospitalOrderRequest $request, HospitalOrder $hospitalOrder): RedirectResponse
    {
        $hospitalOrder->update([
            'status' => 'approved',
            'source_drug_id' => $request->validated('source_drug_id'),
            'quantity_approved' => $request->validated('quantity_approved'),
            'notes' => $request->validated('notes') ?? $hospitalOrder->notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->to(getDashboardHospitalOrderRoute('show', $hospitalOrder))
            ->with('success', 'Hospital order approved. You can now dispatch stock by road to Modilon Hospital.');
    }

    public function reject(RejectHospitalOrderRequest $request, HospitalOrder $hospitalOrder): RedirectResponse
    {
        $hospitalOrder->update([
            'status' => 'rejected',
            'rejection_reason' => $request->validated('rejection_reason'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->to(getDashboardHospitalOrderRoute('show', $hospitalOrder))
            ->with('success', 'Hospital order rejected due to stock unavailability.');
    }

    public function ship(ShipHospitalOrderRequest $request, HospitalOrder $hospitalOrder): RedirectResponse
    {
        $transfer = HospitalShipmentService::ship(
            $hospitalOrder,
            auth()->id(),
            (int) $request->validated('vehicle_id'),
            $request->validated('notes')
        );

        return redirect(getDashboardHospitalShipmentRoute('show', $transfer))
            ->with('success', 'Drugs dispatched by road to Modilon Hospital.');
    }

    public function receive(Request $request, HospitalOrder $hospitalOrder): RedirectResponse
    {
        if (! auth()->user()->hasRole('pharmacy_manager')) {
            abort(403, 'Only Pharmacy Managers can confirm hospital receipt.');
        }

        if ($hospitalOrder->requested_by !== auth()->id()) {
            abort(403, 'You can only receive your own hospital orders.');
        }

        if (! $hospitalOrder->canReceive()) {
            return redirect(getDashboardHospitalOrderRoute('show', $hospitalOrder))
                ->with('error', 'This order is not awaiting receipt.');
        }

        HospitalShipmentService::confirmHospitalReceipt($hospitalOrder, auth()->id(), $request->input('notes'));

        return redirect(getDashboardHospitalOrderRoute('show', $hospitalOrder))
            ->with('success', 'Road delivery received at Modilon Hospital.');
    }
}
