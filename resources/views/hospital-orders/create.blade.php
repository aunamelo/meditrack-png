<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Supply</p>
            <h2 class="heading-page">New Hospital Order</h2>
        </div>
    </x-slot>

    @php
        $defaultItems = old('items', [[
            'stock_key' => '',
            'drug_name' => '',
            'dosage' => '',
            'quantity_requested' => '',
        ]]);
    @endphp

    <x-page-container>
        <x-module.back-link :href="getDashboardHospitalOrderRoute('index')" label="Back to Hospital Orders" class="mb-6" />

        <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm text-slate-700 dark:border-brand-900 dark:bg-brand-950/30 dark:text-slate-300">
            Add one or more medicines from Modilon stock status or the NDoH catalog. They stay on
            <strong>one hospital order</strong> and Lae AMS ships them together in
            <strong>one road delivery</strong>. Suggested quantities cover about
            {{ \App\Services\LmisService::HOSPITAL_MONTHS_OF_COVER }} months.
        </div>

        <div class="module-form-shell">
            <form action="{{ getDashboardHospitalOrderRoute('store') }}" method="POST"
                  class="space-y-6"
                  x-data="createHospitalOrderForm({
                      items: @js($defaultItems),
                      notes: @js(old('notes', '')),
                      notesEdited: @js(filled(old('notes'))),
                      stockOptions: @js($stockOptions->values()),
                  })">
                @csrf

                @if($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-slate-700 dark:bg-slate-900/30">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-ink">Medicines to request</h3>
                            <p class="mt-0.5 text-xs text-muted">Stock-status items appear first. Use + Add medicine for another line.</p>
                        </div>
                        <button
                            type="button"
                            @click="addItem()"
                            class="inline-flex items-center rounded-md border border-brand-600 bg-white px-3 py-1.5 text-xs font-semibold uppercase text-brand-600 hover:bg-brand-50 dark:bg-night-elevated dark:hover:bg-brand-950/40"
                        >
                            + Add medicine
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="space-y-3 rounded-md border border-gray-200 bg-white p-3 dark:border-slate-700 dark:bg-night-elevated">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="m-0 text-xs font-semibold uppercase tracking-wide text-muted" x-text="'Request ' + (index + 1)"></p>
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
                                    <label class="form-label" :for="'stock_key_' + index">Medicine *</label>
                                    <select
                                        :name="`items[${index}][stock_key]`"
                                        :id="'stock_key_' + index"
                                        x-model="item.stock_key"
                                        @change="applyStockOption(index)"
                                        required
                                        class="input-field"
                                    >
                                        <option value="">Select a medicine…</option>
                                        <template x-for="option in availableOptions(index)" :key="option.key">
                                            <option
                                                :value="option.key"
                                                x-text="option.source === 'catalog'
                                                    ? `${option.label} · catalog`
                                                    : `${option.label} · ${option.status_label} · suggest ${Number(option.suggested_quantity).toLocaleString()}`"
                                            ></option>
                                        </template>
                                    </select>
                                </div>

                                <input type="hidden" :name="`items[${index}][drug_name]`" :value="item.drug_name">
                                <input type="hidden" :name="`items[${index}][dosage]`" :value="item.dosage">

                                <div x-show="optionFor(item.stock_key)" x-cloak class="grid grid-cols-2 gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3 text-xs md:grid-cols-4 dark:border-slate-700 dark:bg-slate-900/40">
                                    <div>
                                        <p class="uppercase text-gray-500">On hand</p>
                                        <p class="font-semibold tabular-nums" x-text="optionStat(item.stock_key, 'stock')"></p>
                                    </div>
                                    <div>
                                        <p class="uppercase text-gray-500">AMC (3 mo)</p>
                                        <p class="font-semibold tabular-nums" x-text="optionStat(item.stock_key, 'amc')"></p>
                                    </div>
                                    <div>
                                        <p class="uppercase text-gray-500">Days of stock</p>
                                        <p class="font-semibold tabular-nums" x-text="optionStat(item.stock_key, 'dos')"></p>
                                    </div>
                                    <div>
                                        <p class="uppercase text-gray-500">Suggested</p>
                                        <p class="font-semibold tabular-nums text-brand-700" x-text="optionStat(item.stock_key, 'suggested')"></p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end">
                                    <div>
                                        <label class="form-label" :for="'quantity_' + index">Quantity requested *</label>
                                        <input
                                            type="number"
                                            :name="`items[${index}][quantity_requested]`"
                                            :id="'quantity_' + index"
                                            x-model="item.quantity_requested"
                                            min="1"
                                            required
                                            class="input-field"
                                        >
                                    </div>
                                    <div>
                                        <button
                                            type="button"
                                            class="text-xs font-semibold text-brand-600 hover:underline"
                                            x-show="optionFor(item.stock_key) && Number(optionFor(item.stock_key).suggested_quantity) > 0"
                                            x-cloak
                                            @click="useSuggestedQuantity(index)"
                                        >
                                            Use suggested quantity
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <x-form-label for="notes" optional>Notes (applies to all lines)</x-form-label>
                        <button type="button" x-show="notesEdited" x-cloak @click="notesEdited = false; refreshNotes()" class="text-xs font-medium text-brand-600 hover:underline">Regenerate from form</button>
                    </div>
                    <textarea name="notes" id="notes" rows="3" x-model="notes" @input="notesEdited = true" class="input-field"></textarea>
                    <p class="mt-1 text-xs text-muted">Auto-filled from your selections. Edit to customize.</p>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ getDashboardHospitalOrderRoute('index') }}" class="btn-module-secondary">Cancel</a>
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider" :disabled="!canSubmit">
                        Submit order (<span x-text="items.length"></span> medicine<span x-show="items.length !== 1">s</span>) to Lae AMS
                    </button>
                </div>
            </form>
        </div>
    </x-page-container>

    <script>
        function createHospitalOrderForm(initial) {
            return {
                items: (initial.items && initial.items.length)
                    ? initial.items.map((item) => ({
                        stock_key: item.stock_key ?? '',
                        drug_name: item.drug_name ?? '',
                        dosage: item.dosage ?? '',
                        quantity_requested: item.quantity_requested ?? '',
                    }))
                    : [{ stock_key: '', drug_name: '', dosage: '', quantity_requested: '' }],
                notes: initial.notes ?? '',
                notesEdited: initial.notesEdited ?? false,
                stockOptions: initial.stockOptions ?? [],

                get canSubmit() {
                    return this.items.every((item) => item.stock_key && Number(item.quantity_requested) > 0);
                },

                init() {
                    this.items.forEach((_, index) => this.applyStockOption(index, false));
                    this.$watch('items', () => this.refreshNotes(), { deep: true });
                    if (! this.notesEdited) {
                        this.refreshNotes();
                    }
                },

                optionFor(key) {
                    return this.stockOptions.find((option) => option.key === key) ?? null;
                },

                availableOptions(index) {
                    const selectedElsewhere = this.items
                        .map((item, i) => (i === index ? null : item.stock_key))
                        .filter(Boolean);

                    return this.stockOptions.filter((option) => {
                        const current = this.items[index]?.stock_key;
                        return option.key === current || ! selectedElsewhere.includes(option.key);
                    });
                },

                optionStat(key, field) {
                    const option = this.optionFor(key);
                    if (! option) {
                        return '—';
                    }
                    if (field === 'stock') {
                        return option.source === 'catalog' ? '—' : Number(option.stock_on_hand).toLocaleString();
                    }
                    if (field === 'amc') {
                        return Number(option.amc || 0).toLocaleString();
                    }
                    if (field === 'dos') {
                        return option.days_of_stock === null || option.days_of_stock === undefined
                            ? '—'
                            : Number(option.days_of_stock).toLocaleString();
                    }
                    if (field === 'suggested') {
                        return Number(option.suggested_quantity || 0).toLocaleString();
                    }
                    return '—';
                },

                addItem() {
                    this.items.push({ stock_key: '', drug_name: '', dosage: '', quantity_requested: '' });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                        this.refreshNotes();
                    }
                },

                applyStockOption(index, fillQuantity = true) {
                    const item = this.items[index];
                    const option = this.optionFor(item.stock_key);
                    if (! option) {
                        item.drug_name = '';
                        item.dosage = '';
                        return;
                    }

                    item.drug_name = option.drug_name;
                    item.dosage = option.dosage;
                    if (fillQuantity && ! item.quantity_requested && Number(option.suggested_quantity) > 0) {
                        item.quantity_requested = String(option.suggested_quantity);
                    }
                    this.refreshNotes();
                },

                useSuggestedQuantity(index) {
                    const option = this.optionFor(this.items[index].stock_key);
                    if (option && Number(option.suggested_quantity) > 0) {
                        this.items[index].quantity_requested = String(option.suggested_quantity);
                    }
                },

                buildNotes() {
                    const lines = [];
                    const filled = this.items.filter((item) => item.drug_name && Number(item.quantity_requested) > 0);

                    if (filled.length === 0) {
                        lines.push('Hospital replenishment request from Modilon Hospital.');
                    } else if (filled.length === 1) {
                        const item = filled[0];
                        lines.push(`Hospital replenishment request from Modilon Hospital for ${Number(item.quantity_requested).toLocaleString()} units of ${item.drug_name}${item.dosage ? ` (${item.dosage})` : ''}.`);
                    } else {
                        lines.push(`Hospital replenishment request from Modilon Hospital for ${filled.length} medicines:`);
                        filled.forEach((item) => {
                            lines.push(`- ${item.drug_name}${item.dosage ? ` (${item.dosage})` : ''}: ${Number(item.quantity_requested).toLocaleString()} units`);
                        });
                    }

                    lines.push('Submitted to Lae AMS regional warehouse for stock availability review.');

                    return lines.join('\n');
                },

                refreshNotes() {
                    if (! this.notesEdited) {
                        this.notes = this.buildNotes();
                    }
                },
            };
        }
    </script>
</x-app-layout>
