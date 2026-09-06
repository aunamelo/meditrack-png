<?php

namespace App\Http\Controllers;

use App\Models\Ward;
use App\Models\WardStock;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WardStockController extends Controller
{
    public function index(): View
    {
        $wardStocks = WardStock::with(['ward', 'drug'])
            ->latest('last_issued_at')
            ->paginate(15);

        return view('ward-stocks.index', compact('wardStocks'));
    }

    public function show(Ward $ward): View
    {
        $ward->load(['wardStocks.drug']);
        $wardStocks = $ward->wardStocks;
        
        return view('ward-stocks.show', compact('ward', 'wardStocks'));
    }

    public function updateReorderPoint(Request $request, WardStock $wardStock)
    {
        $validated = $request->validate([
            'reorder_point' => 'required|integer|min:0',
        ]);

        $wardStock->update($validated);

        return back()->with('success', 'Reorder point updated successfully.');
    }
}
