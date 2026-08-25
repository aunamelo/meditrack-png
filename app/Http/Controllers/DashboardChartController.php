<?php

namespace App\Http\Controllers;

use App\Services\DashboardChartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardChartController extends Controller
{
    /**
     * Filterable Modilon dispensing trend (JSON for Chart.js).
     */
    public function dispensing(Request $request): JsonResponse
    {
        if (! auth()->user()->hasAnyRole([
            'admin',
            'procurement_officer',
            'pharmacy_manager',
            'pharmacist',
        ])) {
            abort(403, 'You do not have access to dispensing trends.');
        }

        $drug = $request->string('drug')->trim()->toString();

        return response()->json(
            DashboardChartService::dispensingTrendChart($drug !== '' ? $drug : null)
        );
    }
}
