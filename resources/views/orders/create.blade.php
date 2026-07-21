<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create New Order
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <nav class="flex mb-6" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li>
                                <a href="{{ getDashboardOrderRoute('index') }}" class="text-sm font-medium text-gray-700 hover:text-[#0f766e]">Orders</a>
                            </li>
                            <li><span class="text-sm text-gray-500"> / Create</span></li>
                        </ol>
                    </nav>

                    <form action="{{ getDashboardOrderRoute('store') }}" method="POST">
                        @csrf

                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                                <ul class="text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="drug_id" class="block text-sm font-medium text-gray-700 mb-1">Drug <span class="text-red-500">*</span></label>
                                <select name="drug_id" id="drug_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                    <option value="">Select a drug...</option>
                                    @foreach($drugs as $drug)
                                        <option value="{{ $drug->id }}" {{ old('drug_id') == $drug->id ? 'selected' : '' }}>
                                            {{ $drug->drug_name }} ({{ $drug->dosage }}) — {{ ucfirst(str_replace('_', ' ', $drug->level)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('drug_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="quantity_ordered" class="block text-sm font-medium text-gray-700 mb-1">Quantity Ordered <span class="text-red-500">*</span></label>
                                <input type="number" name="quantity_ordered" id="quantity_ordered" value="{{ old('quantity_ordered') }}" min="1" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('quantity_ordered')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                                <input type="text" name="supplier" id="supplier" value="{{ old('supplier') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('supplier')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Source <span class="text-red-500">*</span></label>
                                <div class="flex gap-4">
                                    @foreach(['overseas' => 'Overseas', 'local' => 'Local', 'donation' => 'Donation'] as $value => $label)
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="source" value="{{ $value }}" {{ old('source', 'overseas') === $value ? 'checked' : '' }} class="text-[#0f766e] focus:ring-[#0f766e]">
                                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('source')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="order_date" class="block text-sm font-medium text-gray-700 mb-1">Order Date <span class="text-red-500">*</span></label>
                                <input type="date" name="order_date" id="order_date" value="{{ old('order_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('order_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" id="expected_delivery_date" value="{{ old('expected_delivery_date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('expected_delivery_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="supplier_invoice" class="block text-sm font-medium text-gray-700 mb-1">Supplier Invoice #</label>
                                <input type="text" name="supplier_invoice" id="supplier_invoice" value="{{ old('supplier_invoice') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('supplier_invoice')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="invoice_amount" class="block text-sm font-medium text-gray-700 mb-1">Invoice Amount</label>
                                <input type="number" name="invoice_amount" id="invoice_amount" value="{{ old('invoice_amount') }}" step="0.01" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('invoice_amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes" id="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">{{ old('notes') }}</textarea>
                                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59]">Create Order</button>
                            <a href="{{ getDashboardOrderRoute('index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
