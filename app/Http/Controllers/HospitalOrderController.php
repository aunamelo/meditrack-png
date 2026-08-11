<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveHospitalOrderRequest;
use App\Http\Requests\ReceiveHospitalOrderRequest;
use App\Http\Requests\RejectHospitalOrderRequest;
use App\Http\Requests\ShipHospitalOrderRequest;
use App\Http\Requests\StoreHospitalOrderRequest;
use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\HospitalOrderItem;
use App\Models\Vehicle;
use App\Services\HospitalOrderNotificationService;
use App\Services\HospitalShipmentService;
use App\Services\LmisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HospitalOrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = HospitalOrder::with(['requester', 'reviewer', 'sourceDrug', 'stockTransfer', 'items']);

        if ($user->hasRole('pharmacy_manager')) {
            $query->where('requested_by', $user->id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('drug_name', 'like', "%{$search}%")
                    ->orWhereHas('items', fn ($items) => $items->where('drug_name', 'like', "%{$search}%"));
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
        $sharedNotes = $request->validated('notes');
        $items = $request->validated('items');
        $first = $items[0];

        // One hospital order + many line items = one future road delivery from Lae AMS.
        $order = DB::transaction(function () use ($items, $sharedNotes, $first) {
            $order = HospitalOrder::create([
                'order_number' => HospitalOrder::generateOrderNumber(),
                'drug_name' => $first['drug_name'],
                'dosage' => $first['dosage'],
                'quantity_requested' => array_sum(array_column($items, 'quantity_requested')),
                'notes' => $sharedNotes,
                'requested_by' => auth()->id(),
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                HospitalOrderItem::create([
                    'hospital_order_id' => $order->id,
                    'drug_name' => $item['drug_name'],
                    'dosage' => $item['dosage'],
                    'quantity_requested' => $item['quantity_requested'],
                ]);
            }

            return $order;
        });

        HospitalOrderNotificationService::notifyStoreManagersOfRequest($order->load('items'));

        $lineCount = count($items);
        $message = $lineCount === 1
            ? "Hospital order {$order->order_number} submitted to Lae AMS."
            : "Hospital order {$order->order_number} with {$lineCount} medicines submitted to Lae AMS (one delivery when shipped).";

        return redirect()
            ->to(getDashboardHospitalOrderRoute('index'))
            ->with('success', $message);
    }

    public function show(HospitalOrder $hospitalOrder): View
    {
        $user = auth()->user();

        if ($user->hasRole('pharmacy_manager') && $hospitalOrder->requested_by !== $user->id) {
            abort(403, 'You can only view your own hospital orders.');
        }

        $hospitalOrder->load([
            'items.sourceDrug',
            'requester',
            'reviewer',
            'sourceDrug',
            'stockTransfer.drug',
            'stockTransfer.items.drug',
            'stockTransfer.destinationDrug',
            'stockTransfer.vehicle',
            'discrepancyReports',
        ]);

        $availableDrugsByItem = [];
        $vehicles = collect();
        if ($user->hasRole('store_manager') && $hospitalOrder->canApprove()) {
            foreach ($hospitalOrder->items as $item) {
                // FEFO: earliest expiry first so pickers default to the soonest-to-expire batch.
                $availableDrugsByItem[$item->id] = Drug::query()
                    ->inInventory()
                    ->atLevel('lae_ams')
                    ->where('quantity_on_hand', '>', 0)
                    ->where('expiry_date', '>=', now())
                    ->where('drug_name', 'like', '%'.$item->drug_name.'%')
                    ->when(filled($item->dosage), fn ($query) => $query->where('dosage', $item->dosage))
                    ->orderBy('expiry_date')
                    ->orderBy('batch_number')
                    ->get();
            }
        }

        if ($user->hasRole('store_manager') && $hospitalOrder->canShip()) {
            $vehicles = Vehicle::query()->active()->atDepot('lae_ams')->orderBy('name')->get();
        }

        return view('hospital-orders.show', compact('hospitalOrder', 'availableDrugsByItem', 'vehicles'));
    }

    /**
     * Printable warehouse pick list for an approved (or shipped) hospital order.
     */
    public function pickList(HospitalOrder $hospitalOrder): View
    {
        if (! auth()->user()->hasRole('store_manager')) {
            abort(403, 'Only Lae AMS Store Managers can print pick lists.');
        }

        if (! in_array($hospitalOrder->status, ['approved', 'shipped', 'received'], true)) {
            abort(403, 'Pick lists are available after the order is approved.');
        }

        $hospitalOrder->load([
            'items.sourceDrug',
            'requester',
            'reviewer',
            'stockTransfer.vehicle',
        ]);

        $report = [
            'generated_at' => now()->format('M d, Y H:i'),
            'generated_by' => auth()->user()->name,
        ];

        return view('hospital-orders.print.pick-list', compact('hospitalOrder', 'report'));
    }

    public function approve(ApproveHospitalOrderRequest $request, HospitalOrder $hospitalOrder): RedirectResponse
    {
        DB::transaction(function () use ($request, $hospitalOrder): void {
            foreach ($request->validated('items') as $row) {
                HospitalOrderItem::query()
                    ->where('hospital_order_id', $hospitalOrder->id)
                    ->where('id', $row['id'])
                    ->update([
                        'source_drug_id' => $row['source_drug_id'],
                        'quantity_approved' => $row['quantity_approved'],
                    ]);
            }

            $hospitalOrder->refresh()->syncHeaderFromItems();
            $hospitalOrder->update([
                'status' => 'approved',
                'notes' => $request->validated('notes') ?? $hospitalOrder->notes,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        $hospitalOrder->load('requester');
        HospitalOrderNotificationService::notifyRequesterOfDecision($hospitalOrder);

        return redirect()
            ->to(getDashboardHospitalOrderRoute('show', $hospitalOrder))
            ->with('success', 'Hospital order approved. You can now dispatch all medicines in one road delivery to Modilon.');
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
        try {
            $transfer = HospitalShipmentService::ship(
                $hospitalOrder,
                auth()->id(),
                (int) $request->validated('vehicle_id'),
                $request->validated('notes'),
                \Illuminate\Support\Carbon::parse($request->validated('expected_arrival_at'))
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->to(getDashboardHospitalOrderRoute('show', $hospitalOrder))
                ->with('error', $e->getMessage());
        }

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

        try {
            HospitalShipmentService::confirmHospitalReceipt($hospitalOrder, auth()->id(), $data);
        } catch (\InvalidArgumentException $e) {
            return redirect(getDashboardHospitalOrderRoute('show', $hospitalOrder))
                ->with('error', $e->getMessage());
        }

        $hospitalOrder->refresh()->load(['requester', 'discrepancyReports']);
        HospitalOrderNotificationService::notifyStoreManagersOfReceipt($hospitalOrder);

        $newDiscrepancy = $hospitalOrder->discrepancyReports()
            ->whereNotIn('id', $existingDiscrepancyIds)
            ->latest('id')
            ->first();

        if ($newDiscrepancy) {
            HospitalOrderNotificationService::notifyStoreManagersOfDiscrepancy($newDiscrepancy);
        }

        $expected = (int) $hospitalOrder->fresh()->load('items')->totalQuantityApproved();
        if ($expected === 0) {
            $expected = (int) ($hospitalOrder->quantity_approved ?? $hospitalOrder->quantity_requested);
        }
        $received = (int) collect($data['items'] ?? [])->sum('quantity_received');
        $message = $received < $expected || $data['condition'] !== 'good'
            ? 'Delivery recorded. A discrepancy report was filed for Lae AMS review.'
            : 'Road delivery verified and received at Modilon Hospital.';

        return redirect(getDashboardHospitalOrderRoute('show', $hospitalOrder))
            ->with('success', $message);
    }
}
