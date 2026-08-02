<?php

use Carbon\CarbonInterface;

if (! function_exists('formatDate')) {
    /**
     * Standard display date for MediTrack PNG (DICT Guideline 10).
     */
    function formatDate(null|string|CarbonInterface $value, string $fallback = 'N/A'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        try {
            $date = $value instanceof CarbonInterface ? $value : \Illuminate\Support\Carbon::parse($value);
        } catch (\Throwable) {
            return $fallback;
        }

        return $date->timezone(config('app.timezone'))->format('d M Y');
    }
}

if (! function_exists('formatDateTime')) {
    /**
     * Standard display date-time for MediTrack PNG (DICT Guideline 10).
     */
    function formatDateTime(null|string|CarbonInterface $value, string $fallback = 'N/A'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        try {
            $date = $value instanceof CarbonInterface ? $value : \Illuminate\Support\Carbon::parse($value);
        } catch (\Throwable) {
            return $fallback;
        }

        return $date->timezone(config('app.timezone'))->format('d M Y H:i');
    }
}
