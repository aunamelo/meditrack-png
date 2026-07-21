<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Procurement Officer Dashboard
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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-6">{{ __("You're logged in as Procurement Officer.") }}</p>
                    <a href="{{ getDashboardDrugRoute('index') }}" class="inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59] focus:outline-none focus:border-[#0f766e] focus:ring ring-[#0f766e] transition ease-in-out duration-150">
                        View Drug Inventory
                    </a>
                    <a href="{{ getDashboardOrderRoute('index') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-white border border-[#0f766e] rounded-md font-semibold text-xs text-[#0f766e] uppercase tracking-widest hover:bg-teal-50 transition ease-in-out duration-150">
                        View Procurement Orders
                    </a>
                    <a href="{{ getDashboardTransferRoute('index') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-white border border-[#0f766e] rounded-md font-semibold text-xs text-[#0f766e] uppercase tracking-widest hover:bg-teal-50 transition ease-in-out duration-150">
                        Lae AMS Road Deliveries
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>