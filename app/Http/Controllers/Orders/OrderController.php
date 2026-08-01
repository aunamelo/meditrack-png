<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdvanceOrderPipelineRequest;
use App\Http\Requests\CancelOrderRequest;
use App\Http\Requests\ReceiveOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Drug;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\Supplier;
use App\Services\LmisService;
use App\Services\OrderNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * List orders — scoped by role (procurement sees own, admin sees all, others read-only).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        if ($user->hasRole('pharmacist')) {
            abort(403, 'Pharmacists cannot view national procurement orders.');
        }

        $query = Order::with(['items.medicine', 'items.drug', 'medicine', 'drug', 'creator', 'registeredSupplier']);

        if ($user->hasRole('procurement_officer')) {
            $query->byCreatedBy($user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%")
                    ->orWhereHas('registeredSupplier', fn ($supplierQuery) => $supplierQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%"))
                    ->orWhereHas('medicine', fn ($medicineQuery) => $medicineQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('drug', fn ($drugQuery) => $drugQuery->where('drug_name', 'like', "%{$search}%"))
                    ->orWhereHas('items.medicine', fn ($medicineQuery) => $medicineQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('items.drug', fn ($drugQuery) => $drugQuery->where('drug_name', 'like', "%{$search}%"));
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

        $medicines = Medicine::query()
            ->active()
            ->with('supplier')
            ->orderBy('name')
            ->orderBy('dosage')
            ->get();

        $suppliers = Supplier::query()->active()->orderBy('country')->orderBy('name')->get();
        $lmisSuggestions = LmisService::procurementSuggestions()->keyBy('medicine_id');

        return view('orders.create', compact('medicines', 'suppliers', 'lmisSuggestions'));
    }

    /**
     * Store a new order with auto-generated order number.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = DB::transaction(function () use ($request) {
            $supplier = Supplier::query()->findOrFail($request->supplier_id);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'medicine_id' => $request->input('items.0.medicine_id'),
                'quantity_ordered' => collect($request->input('items'))->sum('quantity_ordered'),
                'supplier_id' => $supplier->id,
                'supplier' => $supplier->name,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'supplier_invoice' => $request->supplier_invoice,
                'invoice_amount_foreign' => $request->invoice_amount_foreign,
                'invoice_currency' => $request->invoice_currency
                    ? strtoupper($request->invoice_currency)
                    : $supplier->procurementCurrency(),
                'invoice_amount' => $request->invoice_amount,
                'source' => $request->source,
                'notes' => $request->notes,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            foreach ($request->validated('items') as $item) {
                $order->items()->create([
                    'medicine_id' => $item['medicine_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                ]);
            }

            $order->syncLegacyColumnsFromItems();

            return $order;
        });

        $order->load(['items.medicine', 'creator']);

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
        if (auth()->user()->hasRole('pharmacist')) {
            abort(403, 'Pharmacists cannot view national procurement orders.');
        }

        $order->load(['items.medicine', 'items.drug', 'medicine', 'drug', 'creator', 'approver', 'receiver', 'registeredSupplier']);

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

        $medicines = Medicine::query()
            ->active()
            ->orderBy('name')
            ->orderBy('dosage')
            ->get();

        $order->load(['items.medicine']);

        $suppliers = Supplier::query()->active()->orderBy('country')->orderBy('name')->get();

        return view('orders.edit', compact('order', 'medicines', 'suppliers'));
    }

    /**
     * Update a pending order before approval.
     */
    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        DB::transaction(function () use ($request, $order) {
            $supplier = Supplier::query()->findOrFail($request->supplier_id);

            $order->update([
                'supplier_id' => $supplier->id,
                'supplier' => $supplier->name,
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => $request->notes,
            ]);

            $order->items()->delete();

            foreach ($request->validated('items') as $item) {
                $order->items()->create([
                    'medicine_id' => $item['medicine_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                ]);
            }

            $order->syncLegacyColumnsFromItems();
        });

        \Log::info("Order [{$order->order_number}] updated by user ID: ".auth()->id());

        return redirect(getDashboardOrderRoute('show', $order))
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Advance an order through the import pipeline (manufacturing → shipping → customs → FX).
     */
    public function advancePipeline(AdvanceOrderPipelineRequest $request, Order $order): RedirectResponse
    {
        if (! $order->canAdvancePipeline()) {
            return redirect(getDashboardOrderRoute('show', $order))
                ->with('error', 'This order cannot be advanced further in the pipeline.');
        }

        $order->advancePipeline($request->validated('notes'));

        $order->refresh();

        \Log::info("Order [{$order->order_number}] advanced to {$order->status} by user ID: ".auth()->id());

        return redirect(getDashboardOrderRoute('show', $order))
            ->with('success', 'Order moved to: '.$order->statusLabel().'.');
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

        $receivedDate = Carbon::parse($request->received_date);
        $quantitiesByItem = [];

        foreach ($request->validated('items') as $row) {
            $quantitiesByItem[(int) $row['id']] = (int) $row['quantity_received'];
        }

        $order->load('items.medicine');
        $order->receiveItems($quantitiesByItem, auth()->id(), $receivedDate);

        foreach ($order->fresh('items.medicine')->items as $item) {
            $quantityReceived = $quantitiesByItem[$item->id] ?? 0;

            if ($quantityReceived > 0) {
                $this->createDrugFromOrderItem($order, $item, $quantityReceived, $receivedDate);
            }
        }

        if ($request->filled('notes')) {
            $order->update([
                'notes' => trim(($order->notes ?? '')."\n\nReceipt note: ".$request->notes),
            ]);
        }

        $totalReceived = array_sum($quantitiesByItem);

        \Log::info("Order [{$order->order_number}] received ({$totalReceived} units across lines) by user ID: ".auth()->id());

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
     * Create an NDoH-level drug batch when an order line is received.
     */
    private function createDrugFromOrderItem(Order $order, \App\Models\OrderItem $item, int $quantityReceived, Carbon $receivedDate): void
    {
        $medicine = $item->medicine;

        if (! $medicine) {
            return;
        }

        $totalOrdered = max($order->quantity_ordered, 1);
        $lineInvoiceShare = $order->invoice_amount
            ? ($item->quantity_ordered / $totalOrdered) * (float) $order->invoice_amount
            : null;

        $drug = Drug::create([
            'medicine_id' => $medicine->id,
            'drug_name' => $medicine->name,
            'description' => $medicine->description,
            'dosage' => $medicine->dosage,
            'dosage_form' => $medicine->dosage_form,
            'batch_number' => $order->order_number.'-'.$item->id.'-'.now()->format('His'),
            'expiry_date' => $receivedDate->copy()->addYears(2),
            'quantity_received' => $quantityReceived,
            'quantity_on_hand' => $quantityReceived,
            'reorder_point' => $medicine->reorder_point,
            'unit' => $medicine->unit,
            'supplier' => $order->supplier,
            'cost_per_unit' => $lineInvoiceShare
                ? round($lineInvoiceShare / max($item->quantity_ordered, 1), 2)
                : null,
            'level' => 'ndoh',
            'status' => 'active',
            'received_date' => $receivedDate,
            'notes' => "Received from order {$order->order_number} (line item #{$item->id})",
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $item->update(['drug_id' => $drug->id]);
        $order->syncLegacyColumnsFromItems();
    }
}
