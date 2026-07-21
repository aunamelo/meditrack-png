<x-app-layout>
    <x-slot name="header"><div><p class="text-section-label">Reports</p><h2 class="heading-page">Discrepancy Reports</h2></div></x-slot>
    <x-page-container>
        @if(session('success'))<div class="mb-4 rounded-md border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
        <div class="surface-panel p-6">
            @if(auth()->user()->hasRole('pharmacy_manager'))
                <div class="mb-6 flex justify-end"><a href="{{ getDashboardDiscrepancyRoute('create') }}" class="rounded-md bg-brand-600 px-4 py-2 text-xs font-semibold uppercase text-white">Report issue</a></div>
            @endif
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Report #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Issue</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($reports as $report)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium">{{ $report->report_number }}</td>
                            <td class="px-4 py-3 text-sm">{{ $report->getIssueTypeLabel() }}</td>
                            <td class="px-4 py-3 text-sm">{{ ucfirst($report->status) }}</td>
                            <td class="px-4 py-3 text-right text-sm"><a href="{{ getDashboardDiscrepancyRoute('show', $report) }}" class="font-semibold text-brand-600">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No discrepancy reports.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($reports->hasPages())<div class="mt-6">{{ $reports->links() }}</div>@endif
        </div>
    </x-page-container>
</x-app-layout>
