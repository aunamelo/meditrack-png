<?php

namespace App\Http\Controllers;

use App\Services\CurrencyConverterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * List supported currencies for the converter UI.
     */
    public function rates(): JsonResponse
    {
        return response()->json(CurrencyConverterService::getCurrencyList());
    }

    /**
     * Convert an amount to PGK and return equivalents in all supported currencies.
     */
    public function convert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:999999999',
            'from' => 'required|string|size:3',
            'quantity' => 'nullable|integer|min:1|max:999999',
        ]);

        try {
            $result = CurrencyConverterService::convert(
                (float) $validated['amount'],
                $validated['from'],
                isset($validated['quantity']) ? (int) $validated['quantity'] : null,
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }
}
