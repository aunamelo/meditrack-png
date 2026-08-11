<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeStoreManager();

        $query = Vehicle::query()->atDepot('lae_ams');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('registration', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && in_array($request->type, ['truck', 'van', 'ute'], true)) {
            $query->where('type', $request->type);
        }

        if ($request->status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->status !== 'all') {
            $query->where('is_active', true);
        }

        $vehicles = $query->orderBy('name')->paginate(15)->withQueryString();

        $activeShipments = \App\Models\StockTransfer::query()
            ->whereIn('vehicle_id', $vehicles->pluck('id'))
            ->where('status', 'sent')
            ->where('to_level', 'modilon_hospital')
            ->orderByDesc('sent_date')
            ->get()
            ->unique('vehicle_id')
            ->keyBy('vehicle_id');

        return view('vehicles.index', compact('vehicles', 'activeShipments'));
    }

    public function create(): View
    {
        $this->authorizeStoreManager();

        return view('vehicles.create');
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::create([
            'name' => $request->name,
            'registration' => strtoupper(trim($request->registration)),
            'type' => $request->type,
            'depot' => 'lae_ams',
            'is_active' => true,
            'notes' => $request->notes,
        ]);

        return redirect(getDashboardVehicleRoute('show', $vehicle))
            ->with('success', 'Vehicle registered for Lae AMS road deliveries.');
    }

    public function show(Vehicle $vehicle): View
    {
        $this->authorizeStoreManager();
        $this->ensureLaeAmsDepot($vehicle);

        $recentShipments = $vehicle->stockTransfers()
            ->where('to_level', 'modilon_hospital')
            ->with(['hospitalOrder', 'sender'])
            ->latest('sent_date')
            ->limit(10)
            ->get();

        $activeShipment = $vehicle->activeShipment();

        return view('vehicles.show', compact('vehicle', 'recentShipments', 'activeShipment'));
    }

    public function edit(Vehicle $vehicle): View
    {
        $this->authorizeStoreManager();
        $this->ensureLaeAmsDepot($vehicle);

        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->ensureLaeAmsDepot($vehicle);

        $vehicle->update([
            'name' => $request->name,
            'registration' => strtoupper(trim($request->registration)),
            'type' => $request->type,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active', $vehicle->is_active),
        ]);

        return redirect(getDashboardVehicleRoute('show', $vehicle))
            ->with('success', 'Vehicle details updated.');
    }

    public function deactivate(Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeStoreManager();
        $this->ensureLaeAmsDepot($vehicle);

        if ($vehicle->activeShipment()) {
            return back()->with('error', 'This vehicle is currently on a road delivery and cannot be deactivated.');
        }

        $vehicle->update(['is_active' => false]);

        return redirect(getDashboardVehicleRoute('index'))
            ->with('success', 'Vehicle deactivated. It will no longer appear in the ship-order vehicle list.');
    }

    public function activate(Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeStoreManager();
        $this->ensureLaeAmsDepot($vehicle);

        $vehicle->update(['is_active' => true]);

        return redirect(getDashboardVehicleRoute('show', $vehicle))
            ->with('success', 'Vehicle activated for Lae AMS road deliveries.');
    }

    private function authorizeStoreManager(): void
    {
        if (! auth()->user()->hasRole('store_manager')) {
            abort(403, 'Only Lae AMS Store Managers can manage registered vehicles.');
        }
    }

    private function ensureLaeAmsDepot(Vehicle $vehicle): void
    {
        if ($vehicle->depot !== 'lae_ams') {
            abort(404);
        }
    }
}
