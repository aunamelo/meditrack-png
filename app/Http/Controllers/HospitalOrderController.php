<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveHospitalOrderRequest;
use App\Http\Requests\ReceiveHospitalOrderRequest;
use App\Http\Requests\RejectHospitalOrderRequest;
use App\Http\Requests\ShipHospitalOrderRequest;
use App\Http\Requests\StoreHospitalOrderRequest;
use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\Vehicle;
use App\Services\HospitalOrderNotificationService;
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
        $order = HospitalOrder::create([
            'order_number' => HospitalOrder::generateOrderNumber(),
            'drug_name' => $request->validated('drug_name'),
            'dosage' => $request->validated('dosage'),
            'quantity_requested' => $request->validated('quantity_requested'),
            'notes' => $request->validated('notes'),
            'requested_by' => auth()->id(),
            'status' => 'pending',
        ]);

        HospitalOrderNotificationService::notifyStoreManagersOfRequest($order);

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

        $hospitalOrder->load('requester');
        HospitalOrderNotificationService::notifyRequesterOfDecision($hospitalOrder);

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

        $hospitalOrder->load('requester');
        HospitalOrderNotificationService::notifyRequesterOfDecision($hospitalOrder);

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
            $request->validated('notes'),
            \Illuminate\Support\Carbon::parse($request->validated('expected_arrival_at'))
        );

        $hospitalOrder->refresh()->load(['requester', 'stockTransfer']);
        HospitalOrderNotificationService::notifyRequesterOfShipment($hospitalOrder);

        return redirect(getDashboardHospitalShipmentRoute('show', $transfer))
            ->with('success', 'Drugs dispatched by road to Modilon Hospital.');
    }

    public function receive(ReceiveHospitalOrderRequest $request, HospitalOrder $hospitalOrder): RedirectResponse
    {
        if ($hospitalOrder->requested_by !== auth()->id()) {
            abort(403, 'You can only receive your own hospital orders.');
        }

        if (! $hospitalOrder->canReceive()) {
            return redirect(getDashboardHospitalOrderRoute('show', $hospitalOrder))
                ->with('error', 'This order is not awaiting receipt.');
        }

        $data = $request->validated();
        $data['batch_verified'] = true;
        $data['expiry_verified'] = true;

        $existingDiscrepancyIds = $hospitalOrder->discrepancyReports()->pluck('id');

        HospitalShipmentService::confirmHospitalReceipt($hospitalOrder, auth()->id(), $data);

        $hospitalOrder->refresh()->load(['requester', 'discrepancyReports']);
        HospitalOrderNotificationService::notifyStoreManagersOfReceipt($hospitalOrder);

        $newDiscrepancy = $hospitalOrder->discrepancyReports()
            ->whereNotIn('id', $existingDiscrepancyIds)
            ->latest('id')
            ->first();

        if ($newDiscrepancy) {
            HospitalOrderNotificationService::notifyStoreManagersOfDiscrepancy($newDiscrepancy);
        }

        $expected = (int) ($hospitalOrder->quantity_approved ?? $hospitalOrder->quantity_requested);
        $received = (int) $data['quantity_received'];
        $message = $received < $expected || $data['condition'] !== 'good'
            ? 'Delivery recorded. A discrepancy report was filed for Lae AMS review.'
            : 'Road delivery verified and received at Modilon Hospital.';

        return redirect(getDashboardHospitalOrderRoute('show', $hospitalOrder))
            ->with('success', $message);
    }
}
