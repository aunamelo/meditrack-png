<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceiveStockTransferRequest;
use App\Http\Requests\StoreStockTransferRequest;
use App\Models\Drug;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Services\TransferNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    /**
     * List NDoH shipments to Lae AMS — procurement sees transfers they shipped; store manager sees incoming.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = StockTransfer::with(['drug', 'destinationDrug', 'sender', 'approver', 'items.drug'])
            ->whereNull('hospital_order_id')
            ->fromLevel('ndoh')
            ->toLevel('lae_ams');

        if ($user->hasRole('procurement_officer')) {
            $query->sentBy($user->id);
        } elseif ($user->hasRole('store_manager')) {
            $query->whereIn('status', ['sent', 'received', 'cancelled']);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('drug', fn ($drugQuery) => $drugQuery->where('drug_name', 'like', "%{$search}%"))
                    ->orWhereHas('items.drug', fn ($drugQuery) => $drugQuery->where('drug_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('sent_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('sent_date', '<=', $request->date_to);
        }

        $transfers = $query->orderByDesc('created_at')->paginate(15);

        return view('transfers.index', compact('transfers'));
    }

    /**
     * Show form to request shipment from NDoH to Lae AMS (Procurement Officer only).
     */
    public function create(): View
    {
        if (! auth()->user()->hasRole('procurement_officer')) {
            abort(403, 'Only Procurement Officers can request shipments to Lae AMS.');
        }

        $drugs = Drug::query()
            ->inInventory()
            ->atLevel('ndoh')
            ->where('quantity_on_hand', '>', 0)
            ->where('expiry_date', '>=', now())
            ->orderBy('drug_name')
            ->get();

        return view('transfers.create', compact('drugs'));
    }

    /**
     * Create one combined NDoH → Lae AMS delivery (multi-batch) for a single Admin approval.
     */
    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        $transfer = DB::transaction(function () use ($request) {
            $lines = collect($request->input('items', []));
            $firstDrug = Drug::findOrFail($lines->first()['drug_id']);
            $totalSent = (int) $lines->sum(fn ($item) => (int) $item['quantity_sent']);

            $transfer = StockTransfer::create([
                'transfer_number' => StockTransfer::generateTransferNumber(),
                // Header mirrors first line for older screens / notifications.
                'drug_id' => $firstDrug->id,
                'destination_drug_id' => null,
                'batch_number' => $firstDrug->batch_number,
                'quantity_sent' => $totalSent,
                'from_level' => 'ndoh',
                'to_level' => 'lae_ams',
                'sent_date' => $request->sent_date,
                'status' => 'pending',
                'notes' => $request->notes,
                'sent_by' => auth()->id(),
            ]);

            foreach ($lines as $item) {
                $sourceDrug = Drug::findOrFail($item['drug_id']);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'hospital_order_item_id' => null,
                    'drug_id' => $sourceDrug->id,
                    'destination_drug_id' => null,
                    'batch_number' => $sourceDrug->batch_number,
                    'quantity_sent' => (int) $item['quantity_sent'],
                ]);
            }

            return $transfer->load(['drug', 'sender', 'items.drug']);
        });

        TransferNotificationService::notifyAdminsOfPendingShipment($transfer);

        \Log::info("NDoH shipment [{$transfer->transfer_number}] submitted for approval by user ID: ".auth()->id());

        return redirect(getDashboardTransferRoute('show', $transfer))
            ->with('success', 'Combined delivery submitted for NDoH Admin approval. Stock will move after approval.');
    }

    /**
     * Display shipment details.
     */
    public function show(StockTransfer $transfer): View
    {
        $user = auth()->user();

        if ($transfer->hospital_order_id !== null || $transfer->from_level !== 'ndoh' || $transfer->to_level !== 'lae_ams') {
            abort(404);
        }

        if ($user->hasRole('procurement_officer') && $transfer->sent_by !== $user->id) {
            abort(403, 'You can only view your own shipments.');
        }

        if ($user->hasRole('store_manager')) {
            if ($transfer->status === 'pending') {
                abort(403, 'This shipment is awaiting NDoH Admin approval.');
            }
            TransferNotificationService::markTransferNotificationsAsRead($user, $transfer);
        }

        if ($user->hasRole('admin')) {
            TransferNotificationService::markTransferNotificationsAsRead($user, $transfer);
        }

        $transfer->load(['drug', 'destinationDrug', 'sender', 'approver', 'receiver', 'items.drug', 'items.destinationDrug']);

        return view('transfers.show', compact('transfer'));
    }

    /**
     * Approve a pending shipment: deduct NDoH stock and mark in transit (NDoH Admin only).
     */
    public function approve(StockTransfer $transfer): RedirectResponse
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Only NDoH Admin can approve shipments to Lae AMS.');
        }

        if (! $transfer->canApprove()) {
            return redirect(getDashboardTransferRoute('show', $transfer))
                ->with('error', 'This shipment cannot be approved.');
        }

        try {
            $transfer->approve(auth()->id());
        } catch (ValidationException $e) {
            return redirect(getDashboardTransferRoute('show', $transfer))
                ->withErrors($e->errors())
                ->with('error', collect($e->errors())->flatten()->first());
        }

        $transfer->refresh()->load(['drug', 'sender']);

        TransferNotificationService::markTransferNotificationsAsRead(auth()->user(), $transfer);
        TransferNotificationService::notifyStoreManagersOfShipment($transfer);

        \Log::info("NDoH shipment [{$transfer->transfer_number}] approved and sent by user ID: ".auth()->id());

        return redirect(getDashboardTransferRoute('show', $transfer))
            ->with('success', 'Shipment approved and sent to Lae AMS. Store Manager has been notified.');
    }

    /**
     * Confirm receipt at Lae AMS (Store Manager only).
     */
    public function receive(ReceiveStockTransferRequest $request, StockTransfer $transfer): RedirectResponse
    {
        if ($transfer->to_level !== 'lae_ams' || $transfer->hospital_order_id !== null) {
            abort(403, 'This shipment is not destined for Lae AMS.');
        }

        if (! $transfer->canReceive()) {
            return redirect(getDashboardTransferRoute('show', $transfer))
                ->with('error', 'This shipment has already been received, cancelled, or is still awaiting approval.');
        }

        $transfer->receive(auth()->id(), $request->notes);
        $transfer->load(['drug', 'sender']);

        TransferNotificationService::markTransferNotificationsAsRead(auth()->user(), $transfer);
        TransferNotificationService::notifySenderOfReceipt($transfer);

        \Log::info("NDoH shipment [{$transfer->transfer_number}] received at Lae AMS by user ID: ".auth()->id());

        return redirect(getDashboardTransferRoute('show', $transfer))
            ->with('success', 'Shipment confirmed as received. Lae AMS inventory has been updated.');
    }
}
