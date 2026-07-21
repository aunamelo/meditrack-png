<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Order — {{ $order->order_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <nav class="flex mb-6" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li><a href="{{ getDashboardOrderRoute('index') }}" class="text-sm font-medium text-gray-700 hover:text-[#0f766e]">Orders</a></li>
                            <li><a href="{{ getDashboardOrderRoute('show', $order) }}" class="text-sm font-medium text-gray-700 hover:text-[#0f766e]">{{ $order->order_number }}</a></li>
                            <li><span class="text-sm text-gray-500"> / Edit</span></li>
                        </ol>
                    </nav>

                    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4 text-sm text-yellow-800">
                        This order can only be edited before approval.
                    </div>

                    <form action="{{ getDashboardOrderRoute('update', $order) }}" method="POST">
                        @csrf
                        @method('PUT')

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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Drug</label>
                                <input type="text" value="{{ $order->drug->drug_name ?? 'N/A' }}" disabled class="w-full rounded-md border-gray-200 bg-gray-50 text-gray-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Order Date</label>
                                <input type="text" value="{{ $order->formatOrderDate() }}" disabled class="w-full rounded-md border-gray-200 bg-gray-50 text-gray-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                                <input type="text" value="{{ ucfirst($order->source) }}" disabled class="w-full rounded-md border-gray-200 bg-gray-50 text-gray-500">
                            </div>
                            <div>
                                <label for="quantity_ordered" class="block text-sm font-medium text-gray-700 mb-1">Quantity Ordered <span class="text-red-500">*</span></label>
                                <input type="number" name="quantity_ordered" id="quantity_ordered" value="{{ old('quantity_ordered', $order->quantity_ordered) }}" min="1" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('quantity_ordered')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                                <input type="text" name="supplier" id="supplier" value="{{ old('supplier', $order->supplier) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('supplier')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" id="expected_delivery_date" value="{{ old('expected_delivery_date', $order->expected_delivery_date?->format('Y-m-d')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('expected_delivery_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes" id="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">{{ old('notes', $order->notes) }}</textarea>
                                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59]">Update Order</button>
                            <a href="{{ getDashboardOrderRoute('show', $order) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
