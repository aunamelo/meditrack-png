<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Drug - {{ $drug->drug_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Breadcrumb -->
                    <nav class="flex mb-6" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ getDashboardDrugRoute('index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0f766e]">
                                    Drugs
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <a href="{{ getDashboardDrugRoute('show', $drug->id) }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0f766e]">
                                        {{ $drug->drug_name }}
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Edit</span>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <!-- Read-Only Fields Warning -->
                    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Limited Edit Mode</h3>
                                <p class="mt-1 text-sm text-yellow-700">Only notes, reorder point, and storage location can be edited after creation. Other fields are read-only.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form action="{{ getDashboardDrugRoute('update', $drug->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">There were some errors with your submission.</h3>
                                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Read-Only Fields -->
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Drug Name (Read-Only)</label>
                                <input type="text" value="{{ $drug->drug_name }}" disabled
                                    class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Batch Number (Read-Only)</label>
                                <input type="text" value="{{ $drug->batch_number }}" disabled
                                    class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Expiry Date (Read-Only)</label>
                                <input type="text" value="{{ $drug->formatExpiry() }}" disabled
                                    class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Quantity Received (Read-Only)</label>
                                <input type="text" value="{{ $drug->quantity_received }} {{ $drug->unit }}" disabled
                                    class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Dosage (Read-Only)</label>
                                <input type="text" value="{{ $drug->dosage }}" disabled
                                    class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Dosage Form (Read-Only)</label>
                                <input type="text" value="{{ ucfirst($drug->dosage_form) }}" disabled
                                    class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Unit (Read-Only)</label>
                                <input type="text" value="{{ $drug->unit }}" disabled
                                    class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed">
                            </div>

                            <!-- Editable Fields -->
                            <div>
                                <label for="reorder_point" class="block text-sm font-medium text-gray-700 mb-1">Reorder Point <span class="text-red-500">*</span></label>
                                <input type="number" name="reorder_point" id="reorder_point" value="{{ old('reorder_point', $drug->reorder_point) }}" required min="1"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('reorder_point')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="storage_location" class="block text-sm font-medium text-gray-700 mb-1">Storage Location</label>
                                <input type="text" name="storage_location" id="storage_location" value="{{ old('storage_location', $drug->storage_location) }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50"
                                    placeholder="e.g., Shelf A1">
                                @error('storage_location')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes" id="notes" rows="4"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50"
                                    placeholder="Enter any additional notes...">{{ old('notes', $drug->notes) }}</textarea>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="mt-6 flex items-center justify-end gap-4">
                            <a href="{{ getDashboardDrugRoute('show', $drug->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:border-gray-500 focus:ring ring-gray-500 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59] focus:outline-none focus:border-[#0f766e] focus:ring ring-[#0f766e] disabled:opacity-25 transition ease-in-out duration-150">
                                Update Drug
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
