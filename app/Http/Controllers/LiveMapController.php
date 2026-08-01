<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LiveMapController extends Controller
{
    /**
     * The live map page (Leaflet renders it; positions load via data()).
     */
    public function index(): View
    {
        return view('logistics.live-map');
    }

    /**
     * JSON feed of every vehicle currently on a Lae AMS -> Modilon road
     * delivery, with its last known GPS position. Polled by the map view.
     */
    public function data(): JsonResponse
    {
        $user = auth()->user();

        $vehicles = Vehicle::query()
            ->whereHas('stockTransfers', function ($query) use ($user) {
                $query->where('status', 'sent')->where('to_level', 'modilon_hospital');

                if ($user->hasRole('pharmacy_manager')) {
                    $query->whereHas('hospitalOrder', fn ($q) => $q->where('requested_by', $user->id));
                }
            })
            ->with(['latestLocation'])
            ->get();

        $features = $vehicles->map(function (Vehicle $vehicle) {
            $shipment = $vehicle->activeShipment()?->load(['drug', 'hospitalOrder.requester']);
            $location = $vehicle->latestLocation;

            return [
                'vehicle_id' => $vehicle->id,
                'vehicle_label' => $vehicle->displayLabel(),
                'has_location' => $vehicle->hasKnownLocation(),
                'is_stale' => $vehicle->isTrackingStale(),
                'latitude' => $vehicle->last_latitude,
                'longitude' => $vehicle->last_longitude,
                'speed_kmh' => $vehicle->last_speed_kmh,
                'last_ping_at' => $vehicle->last_ping_at?->diffForHumans(),
                'last_ping_iso' => $vehicle->last_ping_at?->toIso8601String(),
                'transfer_number' => $shipment?->transfer_number,
                'drug_name' => $shipment?->drug?->drug_name,
                'quantity_sent' => $shipment?->quantity_sent,
                'trail' => $location ? $vehicle->locations()
                    ->where('recorded_at', '>=', now()->subHours(6))
                    ->orderBy('recorded_at')
                    ->get(['latitude', 'longitude'])
                    ->map(fn ($p) => [(float) $p->latitude, (float) $p->longitude]) : [],
            ];
        });

        return response()->json(['vehicles' => $features]);
    }
}
