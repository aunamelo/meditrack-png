<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Procurement</p>
            <h2 class="heading-page">Edit Order — {{ $order->order_number }}</h2>
        </div>
    </x-slot>

    @php
        $defaultItems = old('items', $order->items->map(fn ($item) => [
            'medicine_id' => (string) $item->medicine_id,
            'quantity_ordered' => (string) $item->quantity_ordered,
        ])->values()->all());

        if (empty($defaultItems)) {
            $defaultItems = [['medicine_id' => (string) $order->medicine_id, 'quantity_ordered' => (string) $order->quantity_ordered]];
        }
    @endphp

    <x-page-container>
        <x-module.back-link :href="getDashboardOrderRoute('show', $order)" :label="'Back to ' . $order->order_number" class="mb-6" />

        <div class="module-form-shell">

                    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4 text-sm text-yellow-800">
                        This order can only be edited before approval.
                    </div>

                    <form action="{{ getDashboardOrderRoute('update', $order) }}" method="POST"
                          x-data="{
                              items: @js($defaultItems),
                              medicineOptions: @js($medicines->map(fn ($medicine) => ['id' => (string) $medicine->id, 'label' => $medicine->displayLabel()])->values()),
                              addItem() { this.items.push({ medicine_id: '', quantity_ordered: '' }); },
                              removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); },
                          }">
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

                        <div class="mb-8 rounded-lg border border-gray-200 bg-gray-50/50 p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">Medicines in this order</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Update line items before the order is approved.</p>
                                </div>
                                <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1.5 bg-white border border-brand-600 rounded-md text-xs font-semibold text-brand-600 uppercase hover:bg-brand-50">
                                    + Add line
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start bg-white rounded-md border border-gray-200 p-3">
                                        <div class="md:col-span-7">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Medicine <span class="text-red-500">*</span></label>
                                            <select :name="`items[${index}][medicine_id]`" x-model="item.medicine_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                                <option value="">Select a medicine...</option>
                                                <template x-for="option in medicineOptions" :key="option.id">
                                                    <option :value="option.id" x-text="option.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                                            <input type="number" :name="`items[${index}][quantity_ordered]`" x-model="item.quantity_ordered" min="1" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                        </div>
                                        <div class="md:col-span-2 flex items-end">
                                            <button type="button" x-show="items.length > 1" @click="removeItem(index)" class="w-full inline-flex justify-center items-center px-3 py-2 border border-red-200 rounded-md text-xs font-semibold text-red-600 uppercase hover:bg-red-50">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Order Date</label>
                                <input type="text" value="{{ $order->formatOrderDate() }}" disabled class="w-full rounded-md border-gray-200 bg-gray-50 text-gray-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                                <input type="text" value="{{ ucfirst($order->source) }}" disabled class="w-full rounded-md border-gray-200 bg-gray-50 text-gray-500">
                            </div>
                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                                <input type="text" name="supplier" id="supplier" value="{{ old('supplier', $order->supplier) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                @error('supplier')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" id="expected_delivery_date" value="{{ old('expected_delivery_date', $order->expected_delivery_date?->format('Y-m-d')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                @error('expected_delivery_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes" id="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">{{ old('notes', $order->notes) }}</textarea>
                                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Update Order</button>
                            <a href="{{ getDashboardOrderRoute('show', $order) }}" class="btn-module-secondary">Cancel</a>
                        </div>
                    </form>
        </div>
    </x-page-container>
</x-app-layout>
