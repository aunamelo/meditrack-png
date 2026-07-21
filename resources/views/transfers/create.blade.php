<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record Road Delivery to Lae AMS
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <nav class="flex mb-6" aria-label="Breadcrumb">
                        <a href="{{ getDashboardTransferRoute('index') }}" class="text-sm font-medium text-gray-700 hover:text-[#0f766e]">Road Deliveries</a>
                        <span class="text-sm text-gray-500 mx-2">/</span>
                        <span class="text-sm text-gray-500">Record</span>
                    </nav>

                    <form action="{{ getDashboardTransferRoute('store') }}" method="POST"
                          x-data="createShipmentForm({
                              drugId: @js(old('drug_id', '')),
                              quantitySent: @js(old('quantity_sent', '')),
                              sentDate: @js(old('sent_date', now()->format('Y-m-d'))),
                              notes: @js(old('notes', '')),
                              notesEdited: @js(filled(old('notes'))),
                              maxQuantity: 0,
                          })">
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
                            <div class="md:col-span-2">
                                <label for="drug_id" class="block text-sm font-medium text-gray-700 mb-1">NDoH Drug <span class="text-red-500">*</span></label>
                                <select name="drug_id" id="drug_id" x-model="drugId" @change="updateMaxQuantity()" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                    <option value="">Select drug to dispatch by road...</option>
                                    @forelse($drugs as $drug)
                                        <option value="{{ $drug->id }}" data-qty="{{ $drug->quantity_on_hand }}" data-batch="{{ $drug->batch_number }}" data-expiry="{{ $drug->formatExpiry() }}" data-name="{{ $drug->drug_name }} ({{ $drug->dosage }})">
                                            {{ $drug->drug_name }} — Batch {{ $drug->batch_number }} ({{ $drug->quantity_on_hand }} available, expires {{ $drug->formatExpiry() }})
                                        </option>
                                    @empty
                                        <option value="" disabled>No NDoH stock available to dispatch</option>
                                    @endforelse
                                </select>
                                @if($drugs->isEmpty())
                                    <p class="mt-1 text-sm text-amber-700">No active NDoH batches with stock. Receive a procurement order first, then return here to dispatch by road to Lae AMS. <a href="{{ getDashboardOrderRoute('index') }}" class="font-medium text-[#0f766e] underline">View orders →</a></p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500">Select a specific NDoH batch with available stock. Stock is deducted from NDoH and a new batch is created at Lae AMS.</p>
                                @endif
                                @error('drug_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="quantity_sent" class="block text-sm font-medium text-gray-700 mb-1">Quantity Sent <span class="text-red-500">*</span></label>
                                <input type="number" name="quantity_sent" id="quantity_sent" x-model="quantitySent" :max="maxQuantity || undefined" min="1" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                <p class="mt-1 text-xs text-gray-500" x-show="maxQuantity > 0" x-cloak>Maximum available: <span x-text="maxQuantity.toLocaleString()"></span> units</p>
                                @error('quantity_sent')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="sent_date" class="block text-sm font-medium text-gray-700 mb-1">Date Sent <span class="text-red-500">*</span></label>
                                <input type="date" name="sent_date" id="sent_date" x-model="sentDate" max="{{ now()->format('Y-m-d') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('sent_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <div class="flex items-center justify-between mb-1">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <button type="button" x-show="notesEdited" x-cloak @click="notesEdited = false; refreshNotes()" class="text-xs font-medium text-[#0f766e] hover:underline">Regenerate from form</button>
                                </div>
                                <textarea name="notes" id="notes" rows="3" x-model="notes" @input="notesEdited = true" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50"></textarea>
                                <p class="mt-1 text-xs text-gray-500">Auto-filled from your selections above. Edit to customize.</p>
                                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-teal-50 border border-teal-200 rounded-md text-sm text-teal-800">
                            Dispatching this road delivery will deduct stock from NDoH and create a new inventory entry at Lae AMS. The Store Manager will be notified to confirm receipt upon arrival by car.
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button type="submit" @if($drugs->isEmpty()) disabled @endif class="inline-flex items-center px-4 py-2 bg-[#0f766e] text-white text-xs font-semibold uppercase rounded-md hover:bg-[#0d5f59] disabled:opacity-50 disabled:cursor-not-allowed">Dispatch to Lae AMS</button>
                            <a href="{{ getDashboardTransferRoute('index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold uppercase rounded-md hover:bg-gray-200">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function createShipmentForm(initial) {
            return {
                drugId: initial.drugId ?? '',
                quantitySent: initial.quantitySent ?? '',
                sentDate: initial.sentDate ?? '',
                notes: initial.notes ?? '',
                notesEdited: initial.notesEdited ?? false,
                maxQuantity: initial.maxQuantity ?? 0,
                init() {
                    this.updateMaxQuantity();

                    ['drugId', 'quantitySent', 'sentDate'].forEach((field) => {
                        this.$watch(field, () => this.refreshNotes());
                    });

                    if (!this.notesEdited) {
                        this.refreshNotes();
                    }
                },
                selectedDrugOption() {
                    const select = document.getElementById('drug_id');
                    if (!select || !this.drugId) {
                        return null;
                    }

                    return select.querySelector(`option[value="${CSS.escape(String(this.drugId))}"]`);
                },
                updateMaxQuantity() {
                    const option = this.selectedDrugOption();
                    this.maxQuantity = option ? parseInt(option.dataset.qty || '0', 10) : 0;
                    this.refreshNotes();
                },
                formatDate(value) {
                    if (!value) {
                        return null;
                    }

                    const date = new Date(`${value}T00:00:00`);

                    if (Number.isNaN(date.getTime())) {
                        return null;
                    }

                    return date.toLocaleDateString('en-GB', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric',
                    });
                },
                buildNotes() {
                    const option = this.selectedDrugOption();
                    const lines = [];

                    if (option) {
                        const drugName = option.dataset.name;
                        const batch = option.dataset.batch;
                        const expiry = option.dataset.expiry;
                        const quantity = Number(this.quantitySent);

                        if (drugName && batch) {
                            let line = `Road delivery of ${drugName}, batch ${batch}`;
                            if (quantity > 0) {
                                line += ` — ${quantity.toLocaleString()} units`;
                            }
                            line += ` from NDoH to Lae AMS by car.`;
                            lines.push(line);
                        }

                        if (expiry) {
                            lines.push(`Batch expires ${expiry}.`);
                        }
                    }

                    const sentDate = this.formatDate(this.sentDate);
                    if (sentDate) {
                        lines.push(`Dispatched by road on ${sentDate}.`);
                    }

                    return lines.join('\n');
                },
                refreshNotes() {
                    if (!this.notesEdited) {
                        this.notes = this.buildNotes();
                    }
                },
            };
        }
    </script>
</x-app-layout>
