<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceiveStockTransferRequest;
use App\Http\Requests\StoreStockTransferRequest;
use App\Models\Drug;
use App\Models\StockTransfer;
use App\Services\TransferNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    /**
     * List road deliveries — procurement sees transfers they dispatched; store manager sees incoming to Lae AMS.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = StockTransfer::with(['drug', 'destinationDrug', 'sender']);

        if ($user->hasRole('procurement_officer')) {
            $query->sentBy($user->id);
        } elseif ($user->hasRole('store_manager')) {
            $query->toLevel('lae_ams');
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('drug', fn ($drugQuery) => $drugQuery->where('drug_name', 'like', "%{$search}%"));
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

        $transfers = $query->orderByDesc('sent_date')->paginate(15);

        return view('transfers.index', compact('transfers'));
    }

    /**
     * Show form to record a road delivery to Lae AMS (Procurement Officer only).
     */
    public function create(): View
    {
        if (! auth()->user()->hasRole('procurement_officer')) {
            abort(403, 'Only Procurement Officers can dispatch road deliveries to Lae AMS.');
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
     * Record road delivery: deduct NDoH stock and create Lae AMS inventory entry.
     */
    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        $sourceDrug = Drug::findOrFail($request->drug_id);

        $transfer = DB::transaction(function () use ($request, $sourceDrug) {
            $transferNumber = StockTransfer::generateTransferNumber();
            $quantitySent = (int) $request->quantity_sent;

            $destinationBatch = $sourceDrug->batch_number.'-LAE-'.now()->format('ymdHis');

            $destinationDrug = Drug::create([
                'drug_name' => $sourceDrug->drug_name,
                'description' => $sourceDrug->description,
                'dosage' => $sourceDrug->dosage,
                'dosage_form' => $sourceDrug->dosage_form,
                'batch_number' => $destinationBatch,
                'expiry_date' => $sourceDrug->expiry_date,
                'quantity_received' => $quantitySent,
                'quantity_on_hand' => $quantitySent,
                'reorder_point' => $sourceDrug->reorder_point,
                'unit' => $sourceDrug->unit,
                'supplier' => $sourceDrug->supplier,
                'cost_per_unit' => $sourceDrug->cost_per_unit,
                'storage_location' => 'Lae AMS Warehouse',
                'level' => 'lae_ams',
                'status' => 'active',
                'received_date' => $request->sent_date,
                'notes' => "Received via transfer {$transferNumber} from NDoH",
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $sourceDrug->update([
                'quantity_on_hand' => $sourceDrug->quantity_on_hand - $quantitySent,
                'last_issued_date' => now(),
                'updated_by' => auth()->id(),
            ]);

            return StockTransfer::create([
                'transfer_number' => $transferNumber,
                'drug_id' => $sourceDrug->id,
                'destination_drug_id' => $destinationDrug->id,
                'batch_number' => $sourceDrug->batch_number,
                'quantity_sent' => $quantitySent,
                'from_level' => 'ndoh',
                'to_level' => 'lae_ams',
                'sent_date' => $request->sent_date,
                'status' => 'sent',
                'notes' => $request->notes,
                'sent_by' => auth()->id(),
            ]);
        });

        $transfer->load(['drug', 'sender']);

        TransferNotificationService::notifyStoreManagersOfShipment($transfer);

        \Log::info("Road delivery [{$transfer->transfer_number}] dispatched to Lae AMS by user ID: ".auth()->id());

        return redirect(getDashboardTransferRoute('show', $transfer))
            ->with('success', 'Road delivery to Lae AMS recorded successfully.');
    }

    /**
     * Display road delivery details.
     */
    public function show(StockTransfer $transfer): View
    {
        $user = auth()->user();

        if ($user->hasRole('procurement_officer') && $transfer->sent_by !== $user->id) {
            abort(403, 'You can only view your own road deliveries.');
        }

        if ($user->hasRole('store_manager') && $transfer->to_level !== 'lae_ams') {
            abort(403, 'You can only view road deliveries to Lae AMS.');
        }

        if ($user->hasRole('store_manager')) {
            TransferNotificationService::markTransferNotificationsAsRead($user, $transfer);
        }

        $transfer->load(['drug', 'destinationDrug', 'sender', 'receiver']);

        return view('transfers.show', compact('transfer'));
    }

    /**
     * Confirm receipt at Lae AMS (Store Manager only).
     */
    public function receive(ReceiveStockTransferRequest $request, StockTransfer $transfer): RedirectResponse
    {
        if ($transfer->to_level !== 'lae_ams') {
            abort(403, 'This road delivery is not destined for Lae AMS.');
        }

        if (! $transfer->canReceive()) {
            return redirect(getDashboardTransferRoute('show', $transfer))
                ->with('error', 'This road delivery has already been received or cancelled.');
        }

        $transfer->receive(auth()->id(), $request->notes);

        TransferNotificationService::markTransferNotificationsAsRead(auth()->user(), $transfer);

        \Log::info("Road delivery [{$transfer->transfer_number}] received at Lae AMS by user ID: ".auth()->id());

        return redirect(getDashboardTransferRoute('show', $transfer))
            ->with('success', 'Road delivery confirmed as received at Lae AMS.');
    }
}
