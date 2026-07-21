<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            NDoH Admin Dashboard
        </h2>
    </x-slot>

    @if (session('login_success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-gradient-to-r from-[#0f766e] to-[#0d5f59] shadow-xl rounded-xl px-5 py-3.5 max-w-sm">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/20 shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <p class="text-base font-semibold text-white">{{ session('login_success') }}</p>
        </div>
    @endif

    @if (session('admin_pending_orders'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 8000)"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed top-6 right-6 z-50 flex items-start gap-3 bg-amber-50 border border-amber-200 shadow-xl rounded-xl px-5 py-3.5 max-w-md {{ session('login_success') ? 'mt-20' : '' }}">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-amber-100 shrink-0">
                <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-amber-900">
                    {{ session('admin_pending_orders') === 1 ? '1 order awaiting approval' : session('admin_pending_orders').' orders awaiting approval' }}
                </p>
                <p class="mt-1 text-sm text-amber-800">Review and approve pending procurement orders below.</p>
            </div>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(($pendingOrderCount ?? 0) > 0)
                <div class="bg-amber-50 border border-amber-200 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-amber-900">
                                    Pending procurement orders
                                    @if(($unreadOrderNotificationCount ?? 0) > 0)
                                        <span class="ml-2 inline-flex items-center rounded-full bg-amber-200 px-2.5 py-0.5 text-xs font-semibold text-amber-900">
                                            {{ $unreadOrderNotificationCount }} new
                                        </span>
                                    @endif
                                </h3>
                                <p class="mt-1 text-sm text-amber-800">
                                    {{ $pendingOrderCount === 1 ? '1 order needs' : $pendingOrderCount.' orders need' }} your approval before suppliers can be confirmed.
                                </p>
                            </div>
                            <a href="{{ getDashboardOrderRoute('index') }}?status=pending" class="shrink-0 inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700">
                                Review orders
                            </a>
                        </div>

                        <ul class="mt-4 divide-y divide-amber-200 rounded-md border border-amber-200 bg-white">
                            @foreach($pendingOrders as $pendingOrder)
                                <li class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $pendingOrder->order_number }}</p>
                                        <p class="text-gray-600">
                                            {{ $pendingOrder->drug->drug_name ?? 'Unknown drug' }} ({{ number_format($pendingOrder->quantity_ordered) }} units)
                                            · {{ $pendingOrder->supplier }}
                                            · by {{ $pendingOrder->creator->name ?? 'Procurement Officer' }}
                                        </p>
                                    </div>
                                    <a href="{{ getDashboardOrderRoute('show', $pendingOrder) }}" class="font-semibold text-[#0f766e] hover:underline">Approve →</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-6">{{ __("You're logged in as NDoH Admin.") }}</p>
                    <a href="{{ route('admin.dashboard.drugs.index') }}" class="inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59] focus:outline-none focus:border-[#0f766e] focus:ring ring-[#0f766e] transition ease-in-out duration-150">
                        View Drug Inventory
                    </a>
                    <a href="{{ getDashboardOrderRoute('index') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-white border border-[#0f766e] rounded-md font-semibold text-xs text-[#0f766e] uppercase tracking-widest hover:bg-teal-50 transition ease-in-out duration-150">
                        View Procurement Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
