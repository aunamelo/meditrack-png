<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Store Manager Dashboard
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

    @if (session('store_pending_shipments'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 8000)"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed top-6 right-6 z-50 flex items-start gap-3 bg-blue-50 border border-blue-200 shadow-xl rounded-xl px-5 py-3.5 max-w-md {{ session('login_success') ? 'mt-20' : '' }}">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 shrink-0">
                <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-blue-900">
                    {{ session('store_pending_shipments') === 1 ? '1 incoming road delivery' : session('store_pending_shipments').' incoming road deliveries' }}
                </p>
                <p class="mt-1 text-sm text-blue-800">Confirm receipt when drugs arrive at Lae AMS.</p>
            </div>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(($pendingShipmentCount ?? 0) > 0)
                <div class="bg-blue-50 border border-blue-200 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-blue-900">
                                    Incoming road deliveries from NDoH
                                    @if(($unreadShipmentNotificationCount ?? 0) > 0)
                                        <span class="ml-2 inline-flex items-center rounded-full bg-blue-200 px-2.5 py-0.5 text-xs font-semibold text-blue-900">
                                            {{ $unreadShipmentNotificationCount }} new
                                        </span>
                                    @endif
                                </h3>
                                <p class="mt-1 text-sm text-blue-800">
                                    {{ $pendingShipmentCount === 1 ? '1 road delivery is awaiting' : $pendingShipmentCount.' road deliveries are awaiting' }} confirmation at Lae AMS.
                                </p>
                            </div>
                            <a href="{{ getDashboardTransferRoute('index') }}?status=sent" class="shrink-0 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                View deliveries
                            </a>
                        </div>

                        <ul class="mt-4 divide-y divide-blue-200 rounded-md border border-blue-200 bg-white">
                            @foreach($pendingShipments as $shipment)
                                <li class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $shipment->transfer_number }}</p>
                                        <p class="text-gray-600">
                                            {{ $shipment->drug->drug_name ?? 'Unknown drug' }} ({{ number_format($shipment->quantity_sent) }} units)
                                            · Batch {{ $shipment->batch_number }}
                                            · dispatched by {{ $shipment->sender->name ?? 'Procurement Officer' }}
                                        </p>
                                    </div>
                                    <a href="{{ getDashboardTransferRoute('show', $shipment) }}" class="font-semibold text-[#0f766e] hover:underline">Confirm →</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-6">{{ __("You're logged in as Store Manager.") }}</p>
                    <a href="{{ route('store-manager.dashboard.drugs.index') }}" class="inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59] focus:outline-none focus:border-[#0f766e] focus:ring ring-[#0f766e] transition ease-in-out duration-150">
                        View Drug Inventory
                    </a>
                    <a href="{{ getDashboardTransferRoute('index') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-white border border-[#0f766e] rounded-md font-semibold text-xs text-[#0f766e] uppercase tracking-widest hover:bg-teal-50 transition ease-in-out duration-150">
                        Lae AMS Road Deliveries
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
