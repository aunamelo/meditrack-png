<x-app-layout>
    <x-slot name="header"><div><p class="text-section-label">Quality</p><h2 class="heading-page">Report Discrepancy</h2></div></x-slot>
    <x-page-container>
        <div class="surface-panel p-6">
            <form action="{{ getDashboardDiscrepancyRoute('store') }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @csrf
                @if($hospitalOrder)
                    <input type="hidden" name="hospital_order_id" value="{{ $hospitalOrder->id }}">
                    <input type="hidden" name="stock_transfer_id" value="{{ $hospitalOrder->stock_transfer_id }}">
                    <div class="md:col-span-2 rounded-md bg-blue-50 p-4 text-sm text-blue-800">Reporting issue for order {{ $hospitalOrder->order_number }} — {{ $hospitalOrder->drug_name }}</div>
                @endif
                <div>
                    <label class="mb-1 block text-sm font-medium">Issue type *</label>
                    <select name="issue_type" required class="w-full rounded-md border-gray-300">
                        @foreach(['short_shipment'=>'Short delivery','damaged'=>'Damaged goods','wrong_item'=>'Wrong item','expired'=>'Expired product','other'=>'Other'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Quantity expected</label>
                    <input type="number" name="quantity_expected" min="0" value="{{ old('quantity_expected', $hospitalOrder->quantity_approved ?? '') }}" class="w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Quantity received</label>
                    <input type="number" name="quantity_received" min="0" value="{{ old('quantity_received') }}" class="w-full rounded-md border-gray-300">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium">Description *</label>
                    <textarea name="description" rows="4" required class="w-full rounded-md border-gray-300">{{ old('description') }}</textarea>
                </div>
                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ getDashboardDiscrepancyRoute('index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold">Cancel</a>
                    <button type="submit" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Submit report</button>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
