{{-- Modal for receiving goods against an approved order --}}
<div x-show="showReceiveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="receive-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showReceiveModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showReceiveModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showReceiveModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ getDashboardOrderRoute('receive', $order) }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4" id="receive-modal-title">Receive Order</h3>

                    @if ($errors->any() && old('_receive_modal'))
                        <div class="mb-4 bg-red-50 border border-red-200 rounded-md p-3 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <dl class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between"><dt class="text-gray-500">Drug</dt><dd class="font-medium">{{ $order->drug->drug_name ?? 'N/A' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Quantity Ordered</dt><dd class="font-medium">{{ number_format($order->quantity_ordered) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Remaining</dt><dd class="font-medium">{{ number_format($remainingQty) }}</dd></div>
                    </dl>

                    <input type="hidden" name="_receive_modal" value="1">

                    <div class="space-y-4">
                        <div>
                            <label for="quantity_received" class="block text-sm font-medium text-gray-700 mb-1">Quantity Received <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity_received" id="quantity_received" value="{{ old('quantity_received', $remainingQty) }}" min="1" max="{{ $remainingQty }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                        </div>
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