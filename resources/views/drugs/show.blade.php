<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $drug->drug_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header Actions -->
                    <div class="flex items-center justify-between mb-6">
                        <a href="{{ getDashboardDrugRoute('index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-[#0f766e]">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Drugs
                        </a>
                        <div class="flex items-center gap-3">
                            @if(auth()->user()->hasRole('procurement_officer') && $drug->level == 'ndoh' || auth()->user()->hasRole('pharmacy_manager') && $drug->level == 'modilon_hospital' || auth()->user()->hasRole('admin'))
                                <a href="{{ getDashboardDrugRoute('edit', $drug->id) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:border-gray-500 focus:ring ring-gray-500 disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>
                            @endif
                            @if(auth()->user()->hasRole('admin'))
                                <form action="{{ getDashboardDrugRoute('destroy', $drug->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this drug?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-100 border border-transparent rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-200 focus:outline-none focus:border-red-500 focus:ring ring-red-500 disabled:opacity-25 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Drug Details (3 Columns) -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Column 1: Drug Information -->
                        <div class="bg-gray-50 rounded-lg p-5">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Drug Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500">Drug Name</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $drug->drug_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Description</p>
                                    <p class="text-sm text-gray-900">{{ $drug->description ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Dosage</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $drug->dosage }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Dosage Form</p>
                                    <p class="text-sm text-gray-900">{{ ucfirst($drug->dosage_form) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Unit</p>
                                    <p class="text-sm text-gray-900">{{ $drug->unit }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2: Stock Status -->
                        <div class="bg-gray-50 rounded-lg p-5">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Stock Status</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500">Quantity On Hand</p>
                                    <p class="text-3xl font-bold text-[#0f766e]">{{ $drug->quantity_on_hand }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Reorder Point</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $drug->reorder_point }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Status</p>
                                    @switch($drug->status_badge)
                                        @case('active')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @break
                                        @case('expiring_soon')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Expiring Soon</span>
                                            @break
                                        @case('expired')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                                            @break
                                        @case('low_stock')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Low Stock</span>
                                            @break
                                        @default
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($drug->status) }}</span>
                                    @endswitch
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Days In Storage</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $drug->days_in_storage }} days</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Days Until Expiry</p>
                                    @if($drug->days_until_expiry < 0)
                                        <p class="text-sm font-bold text-red-600">{{ $drug->days_until_expiry }} days (Expired)</p>
                                    @elseif($drug->days_until_expiry <= 180)
                                        <p class="text-sm font-bold text-yellow-600">{{ $drug->days_until_expiry }} days</p>
                                    @else
                                        <p class="text-sm font-bold text-green-600">{{ $drug->days_until_expiry }} days</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Last Issued Date</p>
                                    <p class="text-sm text-gray-900">{{ $drug->last_issued_date ? $drug->last_issued_date->format('M d, Y') : 'Never' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Column 3: Details -->
                        <div class="bg-gray-50 rounded-lg p-5">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Details</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500">Batch Number</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $drug->batch_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Expiry Date</p>
                                    <p class="text-sm text-gray-900">{{ $drug->formatExpiry() }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Supplier</p>
                                    <p class="text-sm text-gray-900">{{ $drug->supplier ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Cost Per Unit</p>
                                    <p class="text-sm text-gray-900">{{ $drug->cost_per_unit ? 'K' . number_format($drug->cost_per_unit, 2) : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Storage Location</p>
                                    <p class="text-sm text-gray-900">{{ $drug->storage_location ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Level</p>
                                    @switch($drug->level)
                                        @case('ndoh')
                                            <p class="text-sm font-medium text-gray-900">NDoH</p>
                                            @break
                                        @case('lae_ams')
                                            <p class="text-sm font-medium text-gray-900">Lae AMS</p>
                                            @break
                                        @case('modilon_hospital')
                                            <p class="text-sm font-medium text-gray-900">Modilon Hospital</p>
                                            @break
                                    @endswitch
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Received Date</p>
                                    <p class="text-sm text-gray-900">{{ $drug->formatReceivedDate() }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Created By</p>
                                    <p class="text-sm text-gray-900">{{ $drug->createdBy->name }} ({{ $drug->created_at->format('M d, Y') }})</p>
                                </div>
                                @if($drug->updated_by)
                                    <div>
                                        <p class="text-sm text-gray-500">Updated By</p>
                                        <p class="text-sm text-gray-900">{{ $drug->updatedBy->name }} ({{ $drug->updated_at->format('M d, Y') }})</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    @if($drug->notes)
                        <div class="mt-6 bg-gray-50 rounded-lg p-5">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Notes</h3>
                            <p class="text-sm text-gray-900">{{ $drug->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
