<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportCsvService
{
    /**
     * @param  array<int, array<int, string|int|float|null>>  $rows
     */
    public static function download(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
