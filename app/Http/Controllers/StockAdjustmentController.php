<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\Drug;
use App\Models\StockAdjustment;
use App\Services\PortalNavigationService;
use App\Services\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class StockAdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAccess();

        $level = $this->userLevel();
        $query = StockAdjustment::with(['drug', 'adjuster'])->atLevel($level);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('adjustment_number', 'like', "%{$search}%")
                    ->orWhereHas('drug', fn ($drug) => $drug
                        ->where('drug_name', 'like', "%{$search}%")
                        ->orWhere('batch_number', 'like', "%{$search}%"));
            });
        }

        $adjustments = $query->latest('adjusted_at')->paginate(15)->withQueryString();

        return view('stock-adjustments.index', compact('adjustments', 'level'));
    }

    public function create(): View
    {
        $this->authorizeAccess();

        $level = $this->userLevel();
        $batches = Drug::query()
            ->atLevel($level)
            ->inInventory()
            ->orderBy('drug_name')
            ->orderBy('batch_number')
            ->get();

        return view('stock-adjustments.create', compact('batches', 'level'));
    }

    public function store(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $level = $this->userLevel();

        try {
            $adjustment = StockAdjustmentService::post(auth()->user(), $request->validated(), $level);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->to(getDashboardStockAdjustmentRoute('show', $adjustment))
            ->with('success', 'Stock take posted. On-hand quantity updated.');
    }

    public function show(StockAdjustment $stockAdjustment): View
    {
        $this->authorizeAccess();

        if ($stockAdjustment->level !== $this->userLevel()) {
            abort(403, 'You can only view adjustments for your facility.');
        }

        $stockAdjustment->load(['drug', 'adjuster']);

        return view('stock-adjustments.show', compact('stockAdjustment'));
    }

    protected function authorizeAccess(): void
    {
        if (! auth()->user()->hasAnyRole([
            'admin',
            'procurement_officer',
            'store_manager',
            'pharmacy_manager',
        ])) {
            abort(403, 'You do not have access to stock takes.');
        }
    }

    protected function userLevel(): string
    {
        $meta = PortalNavigationService::currentRoleMeta();

        return $meta['inventory_level'] ?? 'ndoh';
    }
}
