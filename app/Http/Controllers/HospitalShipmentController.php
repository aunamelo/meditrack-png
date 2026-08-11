<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalShipmentController extends Controller
{
    /**
     * Track Lae AMS → Modilon Hospital road deliveries.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = StockTransfer::with(['drug', 'destinationDrug', 'sender', 'receiver', 'hospitalOrder', 'vehicle', 'items.drug'])
            ->where('from_level', 'lae_ams')
            ->where('to_level', 'modilon_hospital');

        if ($user->hasRole('pharmacy_manager')) {
            $query->whereHas('hospitalOrder', fn ($q) => $q->where('requested_by', $user->id));
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('drug', fn ($drugQuery) => $drugQuery->where('drug_name', 'like', "%{$search}%"))
                    ->orWhereHas('items.drug', fn ($drugQuery) => $drugQuery->where('drug_name', 'like', "%{$search}%"))
                    ->orWhereHas('vehicle', fn ($vehicleQuery) => $vehicleQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('registration', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transfers = $query->orderByDesc('sent_date')->paginate(15)->withQueryString();

        return view('hospital-shipments.index', compact('transfers'));
    }

    public function show(StockTransfer $transfer): View
    {
        if ($transfer->from_level !== 'lae_ams' || $transfer->to_level !== 'modilon_hospital') {
            abort(404, 'Hospital road delivery not found.');
        }

        $user = auth()->user();

        if ($user->hasRole('pharmacy_manager')) {
            $transfer->load('hospitalOrder');
            if (! $transfer->hospitalOrder || $transfer->hospitalOrder->requested_by !== $user->id) {
                abort(403, 'You can only view road deliveries for your hospital orders.');
            }
        }

        $transfer->load(['drug', 'destinationDrug', 'sender', 'receiver', 'hospitalOrder.requester', 'vehicle', 'items.drug', 'items.destinationDrug']);

        return view('hospital-shipments.show', compact('transfer'));
    }
}
