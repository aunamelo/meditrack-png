<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * Public, no-login pages a driver opens on their own phone via a signed
 * link (sent by the Store Manager when a delivery is dispatched). Signed
 * routes verify the link itself instead of requiring an account, since
 * drivers don't have MediTrack logins.
 */
class DriverTrackController extends Controller
{
    /**
     * The mobile "start tracking" page shown to the driver.
     */
    public function show(Request $request, StockTransfer $transfer): View
    {
        if (! $transfer->isRoadLeg()) {
            abort(404);
        }

        $transfer->load(['vehicle', 'drug', 'hospitalOrder']);

        // The GET link's own signature only covers this route; the ping
        // endpoint needs a signature of its own, valid for the same window.
        $pingUrl = URL::temporarySignedRoute(
            'driver-track.ping',
            now()->addHours(24),
            ['transfer' => $transfer->id]
        );

        return view('driver-track.show', compact('transfer', 'pingUrl'));
    }

    /**
     * Receive one GPS ping from the driver's phone.
     */
    public function ping(Request $request, StockTransfer $transfer): JsonResponse
    {
        if (! $transfer->isRoadLeg()) {
            abort(404);
        }

        if (! $transfer->isTrackable()) {
            return response()->json([
                'message' => 'This delivery is no longer being tracked (it has been received or cancelled).',
                'stop' => true,
            ], 409);
        }

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed_kmh' => ['nullable', 'numeric', 'min:0', 'max:250'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['recorded_at'] = now();
        $data['stock_transfer_id'] = $transfer->id;

        $transfer->vehicle->recordPing($data);

        return response()->json(['message' => 'ok', 'stop' => false]);
    }
}
