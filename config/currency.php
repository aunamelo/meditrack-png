<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Currency
    |--------------------------------------------------------------------------
    |
    | Catalog/order quotes may be INR or CNY; converted PGK is stored as the
    | canonical invoice amount for NDoH approval and inventory costing.
    |
    */
    'base' => 'PGK',

    'cache_ttl' => (int) env('CURRENCY_CACHE_TTL', 3600),

    'api_url' => env('CURRENCY_API_URL', 'https://open.er-api.com/v6/latest/USD'),

    /*
    |--------------------------------------------------------------------------
    | Fallback exchange rates (1 USD = X currency)
    |--------------------------------------------------------------------------
    |
    | Used when the live rate API is unavailable — suitable for limited
    | connectivity environments in PNG.
    |
    */
    'fallback_rates_from_usd' => [
        'USD' => 1,
        'PGK' => 3.85,
        'AUD' => 1.53,
        'NZD' => 1.67,
        'EUR' => 0.92,
        'GBP' => 0.79,
        'INR' => 83.50,
        'CNY' => 7.24,
        'JPY' => 149.50,
        'SGD' => 1.34,
        'MYR' => 4.47,
        'IDR' => 15800,
        'THB' => 35.20,
        'PHP' => 56.50,
        'KRW' => 1330,
        'HKD' => 7.80,
        'CAD' => 1.36,
        'CHF' => 0.88,
        'ZAR' => 18.20,
        'AED' => 3.67,
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency labels (shown in dropdown — extend as needed)
    |--------------------------------------------------------------------------
    */
    'labels' => [
        'PGK' => 'Papua New Guinea Kina',
        'USD' => 'US Dollar',
        'AUD' => 'Australian Dollar',
        'NZD' => 'New Zealand Dollar',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'INR' => 'Indian Rupee',
        'CNY' => 'Chinese Yuan',
        'JPY' => 'Japanese Yen',
        'SGD' => 'Singapore Dollar',
        'MYR' => 'Malaysian Ringgit',
        'IDR' => 'Indonesian Rupiah',
        'THB' => 'Thai Baht',
        'PHP' => 'Philippine Peso',
        'KRW' => 'South Korean Won',
        'HKD' => 'Hong Kong Dollar',
        'CAD' => 'Canadian Dollar',
        'CHF' => 'Swiss Franc',
        'ZAR' => 'South African Rand',
        'AED' => 'UAE Dirham',
    ],

    /*
    |--------------------------------------------------------------------------
    | Currencies pinned to the top of the selector
    |--------------------------------------------------------------------------
    */
    'common' => ['USD', 'AUD', 'NZD', 'INR', 'EUR', 'GBP', 'CNY', 'SGD', 'JPY', 'MYR'],

];
