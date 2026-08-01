<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Analytics</p>
            <h2 class="heading-page">Hospital Report</h2>
        </div>
    </x-slot>

    <x-page-container>
        <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm text-slate-700 dark:border-brand-900/40 dark:bg-brand-950/30 dark:text-slate-200">
            Generate a Modilon pharmacy period summary — inventory, Lae AMS requests, deliveries, dispensing, discrepancies, and stock takes. Print or download CSV after generating.
        </div>

        <div class="surface-panel p-6">
            <form method="GET" action="{{ getDashboardHospitalReportRoute('index') }}" class="mb-6 flex flex-wrap items-end gap-4">
                <div>
                    <label for="date_from" class="mb-1 block text-sm font-medium">From</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $from->toDateString() }}" class="rounded-md border-gray-300 text-sm dark:border-slate-600 dark:bg-slate-800">
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-sm font-medium">To</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $to->toDateString() }}" class="rounded-md border-gray-300 text-sm dark:border-slate-600 dark:bg-slate-800">
                </div>
                <button type="submit" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Generate report</button>
                <a href="{{ getDashboardHospitalReportRoute('print', ['date_from' => $from->toDateString(), 'date_to' => $to->toDateString()]) }}"
                   class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
                   target="_blank" rel="noopener">
                    Print / PDF
                </a>
                <a href="{{ getDashboardHospitalReportRoute('export', ['date_from' => $from->toDateString(), 'date_to' => $to->toDateString()]) }}"
                   class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                    Download CSV
                </a>
            </form>

            @include('reports.partials.hospital-summary', ['report' => $report])
        </div>
    </x-page-container>
</x-app-layout>
