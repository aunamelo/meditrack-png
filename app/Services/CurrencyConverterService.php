<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyConverterService
{
    /**
     * Convert an amount from any supported currency to PGK, with equivalents.
     *
     * @return array{
     *     from: string,
     *     amount: float,
     *     pgk: float,
     *     per_unit_pgk: float|null,
     *     quantity: int|null,
     *     equivalents: array<string, float>,
     *     rates_source: string,
     *     rates_updated_at: string|null
     * }
     */
    public static function convert(float $amount, string $from, ?int $quantity = null): array
    {
        $from = strtoupper($from);
        $rates = self::getRatesFromUsd();
        $pgk = self::convertToPgk($amount, $from, $rates);

        $equivalents = [];
        foreach ($rates['rates'] as $code => $rate) {
            if ($code === 'PGK') {
                $equivalents[$code] = round($pgk, 2);

                continue;
            }

            $usd = self::toUsd($pgk, 'PGK', $rates['rates']);
            $equivalents[$code] = round($usd * $rate, 2);
        }

        ksort($equivalents);

        $perUnit = ($quantity && $quantity > 0) ? round($pgk / $quantity, 4) : null;

        return [
            'from' => $from,
            'amount' => round($amount, 2),
            'pgk' => round($pgk, 2),
            'per_unit_pgk' => $perUnit,
            'quantity' => $quantity,
            'equivalents' => $equivalents,
            'rates_source' => $rates['source'],
            'rates_updated_at' => $rates['updated_at'],
        ];
    }

    /**
     * @return array{currencies: array<int, array{code: string, label: string, common: bool}>, rates_source: string, rates_updated_at: string|null}
     */
    public static function getCurrencyList(): array
    {
        $rates = self::getRatesFromUsd();
        $labels = config('currency.labels', []);
        $common = config('currency.common', []);

        $currencies = collect(array_keys($rates['rates']))
            ->map(fn (string $code) => [
                'code' => $code,
                'label' => $labels[$code] ?? $code,
                'common' => in_array($code, $common, true),
            ])
            ->sortBy(fn (array $item) => [
                ! $item['common'],
                $item['code'],
            ])
            ->values()
            ->all();

        return [
            'currencies' => $currencies,
            'rates_source' => $rates['source'],
            'rates_updated_at' => $rates['updated_at'],
        ];
    }

    /**
     * @return array{rates: array<string, float>, source: string, updated_at: string|null}
     */
    public static function getRatesFromUsd(): array
    {
        $ttl = config('currency.cache_ttl', 3600);

        return Cache::remember('currency_rates_usd', $ttl, function () {
            try {
                $response = Http::timeout(8)->get(config('currency.api_url'));

                if ($response->successful() && $response->json('result') === 'success') {
                    $rates = $response->json('rates');

                    if (is_array($rates) && isset($rates['PGK'])) {
                        return [
                            'rates' => collect($rates)->mapWithKeys(
                                fn ($rate, $code) => [strtoupper($code) => (float) $rate]
                            )->all(),
                            'source' => 'live',
                            'updated_at' => now()->toIso8601String(),
                        ];
                    }
                }
            } catch (\Throwable $exception) {
                Log::warning('Currency API unavailable, using fallback rates.', [
                    'message' => $exception->getMessage(),
                ]);
            }

            return [
                'rates' => config('currency.fallback_rates_from_usd'),
                'source' => 'fallback',
                'updated_at' => null,
            ];
        });
    }

    private static function convertToPgk(float $amount, string $from, array $ratesData): float
    {
        if ($from === 'PGK') {
            return $amount;
        }

        $rates = $ratesData['rates'];

        if (! isset($rates[$from])) {
            throw new \InvalidArgumentException("Unsupported currency: {$from}");
        }

        $usd = self::toUsd($amount, $from, $rates);

        return $usd * $rates['PGK'];
    }

    private static function toUsd(float $amount, string $from, array $rates): float
    {
        if ($from === 'USD') {
            return $amount;
        }

        return $amount / $rates[$from];
    }
}
