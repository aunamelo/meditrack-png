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
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-[#d7ecec] shadow-lg rounded-xl px-5 py-4 max-w-sm">
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-[#eef7f7] shrink-0">
                <svg class="w-4 h-4 text-[#0f766e]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <p class="text-sm text-gray-700">{{ session('login_success') }}</p>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in as Store Manager.") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>