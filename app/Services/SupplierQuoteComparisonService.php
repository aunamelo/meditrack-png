<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\SupplierQuote;
use Illuminate\Support\Collection;

class SupplierQuoteComparisonService
{
    /**
     * Compare supplier quotes for a drug type, with live PGK conversion.
     *
     * @return array{drug: array<string, string>, quantity: int, budget_pgk: float|null, quotes: array<int, array<string, mixed>>}
     */
    public static function compare(Drug $drug, int $quantity, ?float $budgetPgk = null): array
    {
        $quotes = SupplierQuote::query()
            ->active()
            ->forDrugType($drug->drug_name, $drug->dosage)
            ->orderBy('unit_price')
            ->get();

        $compared = $quotes->map(function (SupplierQuote $quote) use ($quantity) {
            try {
                $unitConversion = CurrencyConverterService::convert(
                    (float) $quote->unit_price,
                    $quote->quote_currency,
                );
                $totalConversion = CurrencyConverterService::convert(
                    (float) $quote->unit_price * $quantity,
                    $quote->quote_currency,
                );
            } catch (\InvalidArgumentException) {
                return null;
            }

            return [
                'id' => $quote->id,
                'supplier_name' => $quote->supplier_name,
                'country' => $quote->country,
                'unit_price' => (float) $quote->unit_price,
                'quote_currency' => $quote->quote_currency,
                'unit_price_pgk' => $unitConversion['pgk'],
                'total_pgk' => $totalConversion['pgk'],
                'source' => $quote->source,
                'lead_time_days' => $quote->lead_time_days,
                'min_order_qty' => $quote->min_order_qty,
                'notes' => $quote->notes,
                'meets_minimum' => ! $quote->min_order_qty || $quantity >= $quote->min_order_qty,
            ];
        })->filter()->values();

        $sorted = $compared->sortBy('total_pgk')->values();

        $cheapestTotal = $sorted->first()['total_pgk'] ?? null;

        $enriched = $sorted->map(function (array $quote) use ($budgetPgk, $cheapestTotal) {
            $quote['is_best_price'] = $cheapestTotal !== null && $quote['total_pgk'] <= $cheapestTotal;
            $quote['within_budget'] = $budgetPgk === null || $quote['total_pgk'] <= $budgetPgk;

            return $quote;
        });

        return [
            'drug' => [
                'name' => $drug->drug_name,
                'dosage' => $drug->dosage,
            ],
            'quantity' => $quantity,
            'budget_pgk' => $budgetPgk,
            'quotes' => $enriched->values()->all(),
        ];
    }

    /**
     * @return Collection<int, SupplierQuote>
     */
    public static function quotesForDrug(Drug $drug): Collection
    {
        return SupplierQuote::query()
            ->active()
            ->forDrugType($drug->drug_name, $drug->dosage)
            ->get();
    }
}
