<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Logistics</p>
            <h2 class="heading-page">Ship Stock to Lae AMS</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardTransferRoute('index')" label="Back to Shipments" class="mb-6" />

        <div class="module-form-shell">

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
                                <select name="drug_id" id="drug_id" x-model="drugId" @change="updateMaxQuantity()" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                    <option value="">Select drug to ship to Lae AMS...</option>
                                    @forelse($drugs as $drug)
                                        <option value="{{ $drug->id }}" data-qty="{{ $drug->quantity_on_hand }}" data-batch="{{ $drug->batch_number }}" data-expiry="{{ $drug->formatExpiry() }}" data-name="{{ $drug->drug_name }} ({{ $drug->dosage }})">
                                            {{ $drug->drug_name }} — Batch {{ $drug->batch_number }} ({{ $drug->quantity_on_hand }} available, expires {{ $drug->formatExpiry() }})
                                        </option>
                                    @empty
                                        <option value="" disabled>No NDoH stock available to ship</option>
                                    @endforelse
                                </select>
                                @if($drugs->isEmpty())
                                    <p class="mt-1 text-sm text-amber-700">No active NDoH batches with stock. Receive a procurement order first, then return here to ship stock to Lae AMS. <a href="{{ getDashboardOrderRoute('index') }}" class="font-medium text-brand-600 underline">View orders →</a></p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500">Select a specific NDoH batch with available stock. After NDoH Admin approves, stock is deducted from NDoH and a Lae AMS batch is created.</p>
                                @endif
                                @error('drug_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="quantity_sent" class="block text-sm font-medium text-gray-700 mb-1">Quantity Shipped <span class="text-red-500">*</span></label>
                                <input type="number" name="quantity_sent" id="quantity_sent" x-model="quantitySent" :max="maxQuantity || undefined" min="1" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                <p class="mt-1 text-xs text-gray-500" x-show="maxQuantity > 0" x-cloak>Maximum available: <span x-text="maxQuantity.toLocaleString()"></span> units</p>
                                @error('quantity_sent')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="sent_date" class="block text-sm font-medium text-gray-700 mb-1">Date Shipped <span class="text-red-500">*</span></label>
                                <input type="date" name="sent_date" id="sent_date" x-model="sentDate" max="{{ now()->format('Y-m-d') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                @error('sent_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <div class="flex items-center justify-between mb-1">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <button type="button" x-show="notesEdited" x-cloak @click="notesEdited = false; refreshNotes()" class="text-xs font-medium text-brand-600 hover:underline">Regenerate from form</button>
                                </div>
                                <textarea name="notes" id="notes" rows="3" x-model="notes" @input="notesEdited = true" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50"></textarea>
                                <p class="mt-1 text-xs text-gray-500">Auto-filled from your selections above. Edit to customize.</p>
                                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-brand-50 border border-brand-200 rounded-md text-sm text-brand-800">
                            This creates a shipment request for NDoH Admin approval. Stock is not moved until an admin approves. After approval, the Store Manager is notified to confirm receipt at Lae AMS.
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button type="submit" @if($drugs->isEmpty()) disabled @endif class="btn-brand text-xs uppercase tracking-wider disabled:cursor-not-allowed disabled:opacity-50">Submit for approval</button>
                            <a href="{{ getDashboardTransferRoute('index') }}" class="btn-module-secondary">Cancel</a>
                        </div>
                    </form>
        </div>
    </x-page-container>

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
                            let line = `NDoH shipment of ${drugName}, batch ${batch}`;
                            if (quantity > 0) {
                                line += ` — ${quantity.toLocaleString()} units`;
                            }
                            line += ` to Lae AMS.`;
                            lines.push(line);
                        }

                        if (expiry) {
                            lines.push(`Batch expires ${expiry}.`);
                        }
                    }

                    const sentDate = this.formatDate(this.sentDate);
                    if (sentDate) {
                        lines.push(`Shipped on ${sentDate}.`);
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
