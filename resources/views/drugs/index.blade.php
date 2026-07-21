<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Drug Inventory
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header Section -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Manage drugs across the supply chain</h3>
                            <p class="mt-1 text-sm text-gray-500">View and manage drug inventory at your level</p>
                        </div>
                        @if(auth()->user()->hasRole('procurement_officer'))
                            <a href="{{ getDashboardDrugRoute('create') }}" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59] focus:outline-none focus:border-[#0f766e] focus:ring ring-[#0f766e] disabled:opacity-25 transition ease-in-out duration-150">
                                New Entry
                            </a>
                        @endif
                    </div>

                    <!-- Search and Filter Section -->
                    <form action="{{ getDashboardDrugRoute('index') }}" method="GET" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Search Input -->
                            <div class="md:col-span-2">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <div class="relative">
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Drug name or batch number..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50 pl-10">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                    <option value="">All Statuses</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expiring_soon" {{ request('status') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                </select>
                            </div>

                            <!-- Level Filter (Admin Only) -->
                            @if(auth()->user()->hasRole('admin'))
                                <div>
                                    <label for="level" class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                                    <select name="level" id="level" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                        <option value="">All Levels</option>
                                        <option value="ndoh" {{ request('level') == 'ndoh' ? 'selected' : '' }}>NDoH</option>
                                        <option value="lae_ams" {{ request('level') == 'lae_ams' ? 'selected' : '' }}>Lae AMS</option>
                                        <option value="modilon_hospital" {{ request('level') == 'modilon_hospital' ? 'selected' : '' }}>Modilon Hospital</option>
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59] focus:outline-none focus:border-[#0f766e] focus:ring ring-[#0f766e] disabled:opacity-25 transition ease-in-out duration-150">
                                Search
                            </button>
                            <a href="{{ getDashboardDrugRoute('index') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:border-gray-500 focus:ring ring-gray-500 disabled:opacity-25 transition ease-in-out duration-150">
                                Clear
                            </a>
                        </div>
                    </form>

                    <!-- Results Table -->
                    @if($drugs->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ getDashboardDrugRoute('index', array_merge(request()->except('sort', 'direction'), ['sort' => 'drug_name', 'direction' => request('sort') == 'drug_name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-gray-700">
                                                Drug Name
                                                @if(request('sort') == 'drug_name')
                                                    <span>{{ request('direction') == 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </a>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ getDashboardDrugRoute('index', array_merge(request()->except('sort', 'direction'), ['sort' => 'expiry_date', 'direction' => request('sort') == 'expiry_date' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-gray-700">
                                                Expiry Date
                                                @if(request('sort') == 'expiry_date')
                                                    <span>{{ request('direction') == 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </a>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ getDashboardDrugRoute('index', array_merge(request()->except('sort', 'direction'), ['sort' => 'quantity_on_hand', 'direction' => request('sort') == 'quantity_on_hand' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-gray-700">
                                                Qty On Hand
                                                @if(request('sort') == 'quantity_on_hand')
                                                    <span>{{ request('direction') == 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </a>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Pt</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days to Expiry</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($drugs as $drug)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $drug->drug_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $drug->dosage }} {{ $drug->unit }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $drug->batch_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $drug->formatExpiry() }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $drug->quantity_on_hand }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $drug->reorder_point }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
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
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $drug->status }}</span>
                                                @endswitch
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($drug->days_until_expiry < 0)
                                                    <span class="text-red-600 font-medium">{{ $drug->days_until_expiry }} days</span>
                                                @elseif($drug->days_until_expiry <= 180)
                                                    <span class="text-yellow-600 font-medium">{{ $drug->days_until_expiry }} days</span>
                                                @else
                                                    <span class="text-green-600">{{ $drug->days_until_expiry }} days</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ getDashboardDrugRoute('show', $drug->id) }}" class="text-[#0f766e] hover:text-[#0d5f59] mr-3">View</a>
                                                @if(auth()->user()->hasRole('procurement_officer') && $drug->level == 'ndoh')
                                                    <a href="{{ getDashboardDrugRoute('edit', $drug->id) }}" class="text-[#0f766e] hover:text-[#0d5f59] mr-3">Edit</a>
                                                @endif
                                                @if(auth()->user()->hasRole('admin'))
                                                    <form action="{{ getDashboardDrugRoute('destroy', $drug->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this drug?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $drugs->appends(request()->except('page'))->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No drugs found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new drug entry.</p>
                            @if(auth()->user()->hasRole('procurement_officer'))
                                <div class="mt-6">
                                    <a href="{{ getDashboardDrugRoute('create') }}" class="inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59] focus:outline-none focus:border-[#0f766e] focus:ring ring-[#0f766e] disabled:opacity-25 transition ease-in-out duration-150">
                                        New Entry
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
