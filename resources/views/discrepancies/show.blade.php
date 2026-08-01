<x-app-layout>
    <x-slot name="header"><div><p class="text-section-label">Quality</p><h2 class="heading-page">{{ $discrepancy->report_number }}</h2></div></x-slot>
    <x-page-container>
        @if(session('success'))<div class="mb-4 rounded-md border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="surface-panel p-6 lg:col-span-2 space-y-3 text-sm">
                <p><span class="font-medium">Issue:</span> {{ $discrepancy->getIssueTypeLabel() }}</p>
                <p><span class="font-medium">Status:</span> {{ ucfirst($discrepancy->status) }}</p>
                <p><span class="font-medium">Reported by:</span> {{ $discrepancy->reporter->name ?? 'N/A' }} · {{ $discrepancy->created_at->format('M d, Y') }}</p>
                @if($discrepancy->quantity_expected !== null)<p><span class="font-medium">Expected qty:</span> {{ $discrepancy->quantity_expected }}</p>@endif
                @if($discrepancy->quantity_received !== null)<p><span class="font-medium">Received qty:</span> {{ $discrepancy->quantity_received }}</p>@endif
                <p class="rounded-md bg-gray-50 p-3">{{ $discrepancy->description }}</p>
                @if($discrepancy->resolution_notes)<p class="rounded-md bg-green-50 p-3 text-green-800"><span class="font-medium">Resolution:</span> {{ $discrepancy->resolution_notes }}</p>@endif
                @if($discrepancy->hospitalOrder)<a href="{{ getDashboardHospitalOrderRoute('show', $discrepancy->hospitalOrder) }}" class="font-semibold text-brand-600">View linked order →</a>@endif
            </div>
            @if(auth()->user()->hasRole('store_manager') && $discrepancy->isOpen())
                <div class="surface-panel p-6">
                    <h4 class="mb-4 font-semibold">Resolve report</h4>
                    <form action="{{ getDashboardDiscrepancyRoute('resolve', $discrepancy) }}" method="POST" class="space-y-3">
                        @csrf
                        <textarea name="resolution_notes" rows="4" required placeholder="Resolution notes" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                        <button type="submit" class="w-full rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Mark resolved</button>
                    </form>
                </div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
