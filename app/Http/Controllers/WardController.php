<?php

namespace App\Http\Controllers;

use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WardController extends Controller
{
    public function index(): View
    {
        $wards = Ward::with('creator')->latest()->get();
        return view('wards.index', compact('wards'));
    }

    public function create(): View
    {
        return view('wards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:general,icu,pediatric,maternity,surgical,emergency,outpatient',
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'bed_capacity' => 'nullable|integer|min:0',
        ]);

        $validated['ward_number'] = Ward::generateWardNumber();
        $validated['created_by'] = auth()->id();

        Ward::create($validated);

        return redirect()->route(getDashboardRoutePrefix().'wards.index')
            ->with('success', 'Ward created successfully.');
    }

    public function show(Ward $ward): View
    {
        $ward->load(['creator', 'wardStocks.drug', 'drugIssuances.drug', 'drugUsages.drug', 'drugReturns.drug']);
        return view('wards.show', compact('ward'));
    }

    public function edit(Ward $ward): View
    {
        return view('wards.edit', compact('ward'));
    }

    public function update(Request $request, Ward $ward)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:general,icu,pediatric,maternity,surgical,emergency,outpatient',
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'bed_capacity' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $ward->update($validated);

        return redirect()->route(getDashboardRoutePrefix().'wards.show', $ward)
            ->with('success', 'Ward updated successfully.');
    }

    public function destroy(Ward $ward)
    {
        $ward->delete();

        return redirect()->route(getDashboardRoutePrefix().'wards.index')
            ->with('success', 'Ward deleted successfully.');
    }
}
