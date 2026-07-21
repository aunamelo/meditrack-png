<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrderRequest;
use App\Http\Requests\ReceiveOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Drug;
use App\Models\Order;
use App\Services\OrderNotificationService;
use App\Services\SupplierQuoteComparisonService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * List orders — scoped by role (procurement sees own, admin sees all, others read-only).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Order::with(['drug', 'creator']);

        if ($user->hasRole('procurement_officer')) {
            $query->byCreatedBy($user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%")
                    ->orWhereHas('drug', fn ($drugQuery) => $drugQuery->where('drug_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('order_date', '<=', $request->date_to);
        }

        $orders = $query->orderByDesc('created_at')->paginate(15);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show form to create a new procurement order (Procurement Officer only).
     */
    public function create(): View
    {
        if (! auth()->user()->hasRole('procurement_officer')) {
            abort(403, 'Only Procurement Officers can create orders.');
        }

        $drugs = Drug::query()
            ->atLevel('ndoh')
            ->orderBy('drug_name')
            ->orderByDesc('created_at')
            ->get()
            ->unique(fn (Drug $drug) => $drug->drug_name.'|'.$drug->dosage)
            ->values();

        return view('orders.create', compact('drugs'));
    }

    /**
     * Compare supplier quotes for a drug type (Procurement Officer — JSON for create form).
     */
    public function supplierQuotes(Request $request): JsonResponse
    {
        if (! auth()->user()->hasRole('procurement_officer')) {
            abort(403);
        }

        $validated = $request->validate([
            'drug_id' => 'required|exists:drugs,id',
            'quantity' => 'required|integer|min:1|max:999999',
            'budget' => 'nullable|numeric|min:0|max:999999999',
        ]);

        $drug = Drug::findOrFail($validated['drug_id']);

        return response()->json(
            SupplierQuoteComparisonService::compare(
                $drug,
                (int) $validated['quantity'],
                isset($validated['budget']) ? (float) $validated['budget'] : null,
            )
        );
    }

    /**
     * Store a new order with auto-generated order number.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'drug_id' => $request->drug_id,
            'quantity_ordered' => $request->quantity_ordered,
            'supplier' => $request->supplier,
            'order_date' => $request->order_date,
            'expected_delivery_date' => $request->expected_delivery_date,
            'supplier_invoice' => $request->supplier_invoice,
            'invoice_amount' => $request->invoice_amount,
            'source' => $request->source,
            'notes' => $request->notes,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        $order->load(['drug', 'creator']);

        OrderNotificationService::notifyAdminsOfPendingOrder($order);

        \Log::info("Order [{$order->order_number}] created by user ID: ".auth()->id());

        return redirect(getDashboardOrderRoute('show', $order))
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display order details with role-appropriate actions.
     */
    public function show(Order $order): View
    {
        $order->load(['drug', 'creator', 'approver', 'receiver']);

        if (auth()->user()->hasRole('admin')) {
            OrderNotificationService::markOrderNotificationsAsRead(auth()->user(), $order);
        }

        return view('orders.show', compact('order'));
    }

    /**
     * Show edit form — only pending orders owned by the procurement officer.
     */
    public function edit(Order $order): View
    {
        $user = auth()->user();

        if (! $user->hasRole('procurement_officer') || $order->created_by !== $user->id) {
            abort(403, 'You can only edit your own orders.');
        }

        if ($order->status !== 'pending') {
            abort(403, 'Only pending orders can be edited.');
        }

        return view('orders.edit', compact('order'));
    }

    /**
     * Update a pending order before approval.
     */
    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $order->update([
            'quantity_ordered' => $request->quantity_ordered,
            'supplier' => $request->supplier,
            'expected_delivery_date' => $request->expected_delivery_date,
            'notes' => $request->notes,
        ]);

        \Log::info("Order [{$order->order_number}] updated by user ID: ".auth()->id());

        return redirect(getDashboardOrderRoute('show', $order))
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Approve a pending order (NDoH Admin only).
     */
    public function approve(Order $order): RedirectResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only NDoH Admin can approve orders.');
        }

        if (! $order->canApprove()) {
            return redirect(getDashboardOrderRoute('show', $order))
                ->with('error', 'This order cannot be approved.');
        }

        $order->approve(auth()->id());

        if ($admin = auth()->user()) {
            OrderNotificationService::markOrderNotificationsAsRead($admin, $order);
        }

        \Log::info("Order [{$order->order_number}] approved by user ID: ".auth()->id());

        return redirect(getDashboardOrderRoute('show', $order))
            ->with('success', 'Order approved successfully.');
    }

    /**
     * Receive goods against an approved order and create NDoH drug inventory.
     */
    public function receive(ReceiveOrderRequest $request, Order $order): RedirectResponse
    {
        if (! $order->canReceive()) {
            return redirect(getDashboardOrderRoute('show', $order))
                ->with('error', 'This order cannot be received.');
        }

        $quantityReceived = (int) $request->quantity_received;
        $receivedDate = Carbon::parse($request->received_date);

        $order->receive($quantityReceived, auth()->id(), $receivedDate);

        if ($request->filled('notes')) {
            $order->update([
                'notes' => trim(($order->notes ?? '')."\n\nReceipt note: ".$request->notes),
            ]);
        }

        $this->createDrugFromOrder($order, $quantityReceived, $receivedDate);

        \Log::info("Order [{$order->order_number}] received ({$quantityReceived} units) by user ID: ".auth()->id());

        return redirect(getDashboardOrderRoute('show', $order))
            ->with('success', 'Order received successfully. Drug inventory updated.');
    }

    /**
     * Cancel a pending order.
     */
    public function cancel(CancelOrderRequest $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return redirect(getDashboardOrderRoute('show', $order))
                ->with('error', 'Only pending orders can be cancelled.');
        }

        $order->cancel($request->reason);

        \Log::info("Order [{$order->order_number}] cancelled by user ID: ".auth()->id());

        return redirect(getDashboardOrderRoute('index'))
            ->with('success', 'Order cancelled successfully.');
    }

    /**
     * Soft-delete a pending order (NDoH Admin only).
     */
    public function destroy(Order $order): RedirectResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only NDoH Admin can delete orders.');
        }

        if ($order->status !== 'pending') {
            return redirect(getDashboardOrderRoute('show', $order))
                ->with('error', 'Only pending orders can be deleted.');
        }

        $orderNumber = $order->order_number;
        $order->delete();

        \Log::info("Order [{$orderNumber}] deleted by user ID: ".auth()->id());

        return redirect(getDashboardOrderRoute('index'))
            ->with('success', 'Order deleted successfully.');
    }

    /**
     * Create an NDoH-level drug batch when an order is received.
     */
    private function createDrugFromOrder(Order $order, int $quantityReceived, Carbon $receivedDate): void
    {
        $referenceDrug = $order->drug;

        Drug::create([
            'drug_name' => $referenceDrug->drug_name,
            'description' => $referenceDrug->description,
            'dosage' => $referenceDrug->dosage,
            'dosage_form' => $referenceDrug->dosage_form,
            'batch_number' => $order->order_number.'-'.now()->format('His'),
            'expiry_date' => $referenceDrug->expiry_date->gt($receivedDate)
                ? $referenceDrug->expiry_date
                : $receivedDate->copy()->addYears(2),
            'quantity_received' => $quantityReceived,
            'quantity_on_hand' => $quantityReceived,
            'reorder_point' => $referenceDrug->reorder_point,
            'unit' => $referenceDrug->unit,
            'supplier' => $order->supplier,
            'cost_per_unit' => $order->invoice_amount
                ? round($order->invoice_amount / max($order->quantity_ordered, 1), 2)
                : $referenceDrug->cost_per_unit,
            'storage_location' => $referenceDrug->storage_location,
            'level' => 'ndoh',
            'status' => 'active',
            'received_date' => $receivedDate,
            'notes' => "Received from order {$order->order_number}",
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
    }
}
