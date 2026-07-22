{{-- Modal for receiving goods against an approved order (per line item) --}}
<div x-show="showReceiveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="receive-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showReceiveModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showReceiveModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showReceiveModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form action="{{ getDashboardOrderRoute('receive', $order) }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-1" id="receive-modal-title">Receive Order</h3>
                    <p class="text-sm text-gray-500 mb-4">Enter quantities received for each medicine line. Leave at 0 to skip a line this delivery.</p>

                    @if ($errors->any() && old('_receive_modal'))
                        <div class="mb-4 bg-red-50 border border-red-200 rounded-md p-3 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <input type="hidden" name="_receive_modal" value="1">

                    <div class="overflow-x-auto rounded-lg border border-gray-200 mb-4">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Drug</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ordered</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Already in</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Receive now</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($order->items as $index => $item)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ $item->drug->drug_name ?? 'Unknown' }}
                                            <span class="block text-xs text-gray-500">{{ $item->drug->dosage ?? '' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($item->quantity_ordered) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($item->quantity_received ?? 0) }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($item->remainingQuantity()) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                            <input type="number"
                                                   name="items[{{ $index }}][quantity_received]"
                                                   value="{{ old('items.'.$index.'.quantity_received', $item->remainingQuantity() > 0 ? $item->remainingQuantity() : 0) }}"
                                                   min="0"
                                                   max="{{ $item->remainingQuantity() }}"
                                                   class="w-24 rounded-md border-gray-300 text-sm shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $order->drug->drug_name ?? 'Unknown' }}</td>
                                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($order->quantity_ordered) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($order->quantity_received ?? 0) }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format(max(0, $order->quantity_ordered - ($order->quantity_received ?? 0))) }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-500">No line items</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="received_date" class="block text-sm font-medium text-gray-700 mb-1">Received Date <span class="text-red-500">*</span></label>
                            <input type="date" name="received_date" id="received_date" value="{{ old('received_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="receive_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" id="receive_notes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#0f766e] text-base font-medium text-white hover:bg-[#0d5f59] sm:w-auto sm:text-sm">Receive & Confirm</button>
                    <button type="button" @click="showReceiveModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
