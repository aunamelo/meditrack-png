<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Logistics</p>
            <h2 class="heading-page">Ship Stock to Lae AMS</h2>
        </div>
    </x-slot>

    @php
        $defaultItems = old('items', [[
            'drug_id' => '',
            'quantity_sent' => '',
        ]]);
    @endphp

    <x-page-container>
        <x-module.back-link :href="getDashboardTransferRoute('index')" label="Back to Shipments" class="mb-6" />

        <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm text-slate-700 dark:border-brand-900 dark:bg-brand-950/30 dark:text-slate-300">
            Add one or more NDoH batches below. They stay on
            <strong>one combined delivery</strong> for a single NDoH Admin approval.
            Stock is deducted when that delivery is approved; Lae AMS inventory updates when the Store Manager confirms receipt.
        </div>

        <div class="module-form-shell">
            <form action="{{ getDashboardTransferRoute('store') }}" method="POST"
                  class="space-y-6"
                  x-data="createShipmentForm({
                      items: @js($defaultItems),
                      sentDate: @js(old('sent_date', now()->format('Y-m-d'))),
                      notes: @js(old('notes', '')),
                      notesEdited: @js(filled(old('notes'))),
                      drugOptions: @js($drugs->map(fn ($d) => [
                          'id' => $d->id,
                          'qty' => $d->quantity_on_hand,
                          'batch' => $d->batch_number,
                          'expiry' => $d->formatExpiry(),
                          'name' => $d->drug_name.' ('.$d->dosage.')',
                          'label' => $d->drug_name.' — Batch '.$d->batch_number.' ('.$d->quantity_on_hand.' available, expires '.$d->formatExpiry().')',
                      ])->values()),
                  })">
                @csrf

                {{-- Hidden target so the shared QR scanner can set drug_id --}}
                <input type="hidden" id="drug_id" x-ref="qrDrugId" @change="applyQrDrug($event.target.value)" value="">

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                        <ul class="text-sm text-red-700 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-slate-700 dark:bg-slate-900/30">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-ink">Batches on this delivery</h3>
                            <p class="mt-0.5 text-xs text-muted">Use + Add batch for another line. All lines ship together.</p>
                        </div>
                        <button
                            type="button"
                            @click="addItem()"
                            @if($drugs->isEmpty()) disabled @endif
                            class="inline-flex items-center rounded-md border border-brand-600 bg-white px-3 py-1.5 text-xs font-semibold uppercase text-brand-600 hover:bg-brand-50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-night-elevated dark:hover:bg-brand-950/40"
                        >
                            + Add batch
                        </button>
                    </div>

                    @if($drugs->isEmpty())
                        <p class="text-sm text-amber-700">No active NDoH batches with stock. Receive a procurement order first, then return here to ship stock to Lae AMS. <a href="{{ getDashboardOrderRoute('index') }}" class="font-medium text-brand-600 underline">View orders →</a></p>
                    @else
                        <div class="space-y-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="space-y-3 rounded-md border border-gray-200 bg-white p-3 dark:border-slate-700 dark:bg-night-elevated">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="m-0 text-xs font-semibold uppercase tracking-wide text-muted" x-text="'Batch ' + (index + 1)"></p>
                                        <button
                                            type="button"
                                            x-show="items.length > 1"
                                            x-cloak
                                            @click="removeItem(index)"
                                            class="text-xs font-semibold uppercase text-rose-600 hover:underline"
                                        >
                                            Remove
                                        </button>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1" :for="'drug_id_' + index">NDoH Drug <span class="text-red-500">*</span></label>
                                        <select
                                            :name="`items[${index}][drug_id]`"
                                            :id="'drug_id_' + index"
                                            x-model="item.drug_id"
                                            @change="onDrugChange(index)"
                                            required
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50"
                                        >
                                            <option value="">Select drug to ship to Lae AMS...</option>
                                            <template x-for="option in availableOptions(index)" :key="option.id">
                                                <option :value="String(option.id)" x-text="option.label"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1" :for="'quantity_sent_' + index">Quantity Shipped <span class="text-red-500">*</span></label>
                                        <input
                                            type="number"
                                            :name="`items[${index}][quantity_sent]`"
                                            :id="'quantity_sent_' + index"
                                            x-model="item.quantity_sent"
                                            :max="maxFor(item.drug_id) || undefined"
                                            min="1"
                                            required
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50"
                                        >
                                        <p class="mt-1 text-xs text-gray-500" x-show="maxFor(item.drug_id) > 0" x-cloak>
                                            Maximum available: <span x-text="maxFor(item.drug_id).toLocaleString()"></span> units
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4">
                            <x-qr-scanner title="Scan batch QR" hint="Scan a batch label to fill the first empty line (or add a new line)." />
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <p class="mt-1 text-xs text-gray-500">Applies to this combined delivery. Auto-filled from your selections; edit to customize.</p>
                        @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6 p-4 bg-brand-50 border border-brand-200 rounded-md text-sm text-brand-800">
                    Submitting creates <strong>one delivery</strong> with every batch line for a single NDoH Admin approval. Stock is deducted from NDoH when that delivery is approved.
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" @if($drugs->isEmpty()) disabled @endif class="btn-brand text-xs uppercase tracking-wider disabled:cursor-not-allowed disabled:opacity-50">
                        Submit for approval
                    </button>
                    <a href="{{ getDashboardTransferRoute('index') }}" class="btn-module-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </x-page-container>

    <script>
        function createShipmentForm(initial) {
            return {
                items: (initial.items || []).map((item) => ({
                    drug_id: item.drug_id != null && item.drug_id !== '' ? String(item.drug_id) : '',
                    quantity_sent: item.quantity_sent ?? '',
                })),
                sentDate: initial.sentDate ?? '',
                notes: initial.notes ?? '',
                notesEdited: initial.notesEdited ?? false,
                drugOptions: initial.drugOptions ?? [],
                init() {
                    this.$watch('items', () => this.refreshNotes(), { deep: true });
                    this.$watch('sentDate', () => this.refreshNotes());

                    if (!this.notesEdited) {
                        this.refreshNotes();
                    }
                },
                optionById(id) {
                    if (!id) {
                        return null;
                    }
                    return this.drugOptions.find((o) => String(o.id) === String(id)) || null;
                },
                maxFor(drugId) {
                    const option = this.optionById(drugId);
                    return option ? Number(option.qty || 0) : 0;
                },
                usedDrugIds(exceptIndex) {
                    return this.items
                        .map((item, index) => (index === exceptIndex ? null : item.drug_id))
                        .filter((id) => id !== null && id !== '');
                },
                availableOptions(index) {
                    const used = new Set(this.usedDrugIds(index).map(String));
                    return this.drugOptions.filter((option) => {
                        const id = String(option.id);
                        return !used.has(id) || String(this.items[index]?.drug_id) === id;
                    });
                },
                addItem() {
                    this.items.push({ drug_id: '', quantity_sent: '' });
                },
                removeItem(index) {
                    if (this.items.length <= 1) {
                        return;
                    }
                    this.items.splice(index, 1);
                },
                onDrugChange(index) {
                    const item = this.items[index];
                    const max = this.maxFor(item.drug_id);
                    const qty = Number(item.quantity_sent);
                    if (max > 0 && qty > max) {
                        item.quantity_sent = String(max);
                    }
                    this.refreshNotes();
                },
                applyQrDrug(rawId) {
                    if (!rawId) {
                        return;
                    }

                    const id = String(rawId);
                    if (!this.optionById(id)) {
                        return;
                    }

                    let targetIndex = this.items.findIndex((item) => !item.drug_id);
                    if (targetIndex === -1) {
                        if (this.usedDrugIds(-1).map(String).includes(id)) {
                            return;
                        }
                        this.addItem();
                        targetIndex = this.items.length - 1;
                    }

                    if (this.usedDrugIds(targetIndex).map(String).includes(id)) {
                        return;
                    }

                    this.items[targetIndex].drug_id = id;
                    this.onDrugChange(targetIndex);

                    if (this.$refs.qrDrugId) {
                        this.$refs.qrDrugId.value = '';
                    }
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
                    const lines = [];
                    const filled = this.items.filter((item) => item.drug_id);

                    if (filled.length > 1) {
                        lines.push(`Combined NDoH delivery of ${filled.length} batches to Lae AMS.`);
                    }

                    filled.forEach((item, index) => {
                        const option = this.optionById(item.drug_id);
                        if (!option) {
                            return;
                        }

                        const quantity = Number(item.quantity_sent);
                        let line = `Batch ${index + 1}: ${option.name}, batch ${option.batch}`;
                        if (quantity > 0) {
                            line += ` — ${quantity.toLocaleString()} units`;
                        }
                        lines.push(line);

                        if (option.expiry) {
                            lines.push(`Expires ${option.expiry}.`);
                        }
                    });

                    const sentDate = this.formatDate(this.sentDate);
                    if (sentDate && filled.length) {
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
