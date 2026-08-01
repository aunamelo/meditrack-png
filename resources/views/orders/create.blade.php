<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Procurement</p>
            <h2 class="heading-page">Create New Order</h2>
        </div>
    </x-slot>

    @php
        $defaultItems = old('items', [['medicine_id' => '', 'quantity_ordered' => '']]);
    @endphp

    <x-page-container>
        <x-module.back-link :href="getDashboardOrderRoute('index')" label="Back to Orders" class="mb-6" />

        <div class="module-form-shell">

                    <form action="{{ getDashboardOrderRoute('store') }}" method="POST"
                          @pgk-applied="applyPgkAmount($event.detail)"
                          x-data="createOrderForm({
                              items: @js($defaultItems),
                              supplierId: @js(old('supplier_id', '')),
                              source: @js(old('source', 'overseas')),
                              orderDate: @js(old('order_date', now()->format('Y-m-d'))),
                              expectedDeliveryDate: @js(old('expected_delivery_date', '')),
                              supplierInvoice: @js(old('supplier_invoice', '')),
                              invoiceAmount: @js(old('invoice_amount', '')),
                              invoiceAmountForeign: @js(old('invoice_amount_foreign', '')),
                              invoiceCurrency: @js(old('invoice_currency', '')),
                              notes: @js(old('notes', '')),
                              notesEdited: @js(filled(old('notes'))),
                              medicineOptions: @js($medicines->map(fn ($medicine) => [
                                  'id' => (string) $medicine->id,
                                  'label' => $medicine->displayLabel(),
                                  'supplier_id' => $medicine->supplier_id ? (string) $medicine->supplier_id : '',
                                  'unit_cost' => $medicine->unit_cost !== null ? (float) $medicine->unit_cost : null,
                                  'currency' => $medicine->quoteCurrency(),
                                  'unit' => $medicine->unit,
                              ])->values()),
                              supplierOptions: @js($suppliers->map(fn ($supplier) => [
                                  'id' => (string) $supplier->id,
                                  'name' => $supplier->name,
                                  'label' => $supplier->displayLabel(),
                                  'country' => $supplier->country,
                                  'currency' => $supplier->procurementCurrency(),
                              ])->values()),
                              lmisSuggestions: @js($lmisSuggestions),
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

                        <div class="mb-8 rounded-lg border border-gray-200 bg-gray-50/50 p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">Medicines in this order</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Select medicines from the NDoH catalog. Suggested quantities use Modilon consumption and corridor stock ({{ \App\Services\LmisService::PROCUREMENT_MONTHS_OF_COVER }} months cover).</p>
                                </div>
                                <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1.5 bg-white border border-brand-600 rounded-md text-xs font-semibold text-brand-600 uppercase hover:bg-brand-50">
                                    + Add line
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="bg-white rounded-md border border-gray-200 p-3 space-y-3">
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                            <div class="md:col-span-5">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Medicine <span class="text-red-500">*</span></label>
                                                <select :name="`items[${index}][medicine_id]`" x-model="item.medicine_id" @change="applySuggestion(index)" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                                    <option value="">Select a medicine...</option>
                                                    <template x-for="option in medicineOptions" :key="option.id">
                                                        <option :value="option.id" x-text="option.label"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                                                <input type="number" :name="`items[${index}][quantity_ordered]`" x-model="item.quantity_ordered" min="1" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                            </div>
                                            <div class="md:col-span-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Unit cost / line total</label>
                                                <p class="rounded-md border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-800" x-text="lineCostLabel(item)"></p>
                                            </div>
                                            <div class="md:col-span-2 flex items-end">
                                                <button type="button" x-show="items.length > 1" @click="removeItem(index)" class="w-full inline-flex justify-center items-center px-3 py-2 border border-red-200 rounded-md text-xs font-semibold text-red-600 uppercase hover:bg-red-50">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                        <div class="rounded-md border border-dashed border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600" x-show="suggestionFor(item.medicine_id)" x-cloak>
                                            <template x-if="suggestionFor(item.medicine_id)">
                                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                                    <span>Status: <strong x-text="suggestionFor(item.medicine_id).status_label"></strong></span>
                                                    <span>AMC: <strong x-text="Number(suggestionFor(item.medicine_id).amc).toLocaleString()"></strong></span>
                                                    <span>Corridor SOH: <strong x-text="Number(suggestionFor(item.medicine_id).stock_on_hand).toLocaleString()"></strong></span>
                                                    <span>On order: <strong x-text="Number(suggestionFor(item.medicine_id).pending_on_order).toLocaleString()"></strong></span>
                                                    <span>Suggested: <strong class="text-brand-700" x-text="Number(suggestionFor(item.medicine_id).suggested_quantity).toLocaleString()"></strong></span>
                                                    <button type="button" class="font-semibold text-brand-600 hover:underline" @click="useSuggestion(index)" x-show="Number(suggestionFor(item.medicine_id).suggested_quantity) > 0">Use suggestion</button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4 flex flex-wrap items-end justify-between gap-3 rounded-md border border-brand-100 bg-white px-4 py-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-700">Supplier currency total</p>
                                    <p class="mt-1 font-display text-2xl font-bold text-ink" x-text="foreignTotalLabel"></p>
                                    <p class="mt-1 text-xs text-amber-700" x-show="mixedCurrencies" x-cloak>Lines use different currencies — pick medicines from one country (India or China) so the total can be converted cleanly.</p>
                                    <p class="mt-1 text-xs text-gray-500" x-show="!mixedCurrencies && !foreignTotal" x-cloak>Add medicines with unit costs to calculate the foreign total.</p>
                                </div>
                                <p class="text-xs text-gray-500 max-w-sm text-right">India quotes in INR, China in CNY. Convert below to PGK before sending to NDoH Admin.</p>
                            </div>

                            @if($medicines->isEmpty())
                                <p class="mt-3 text-sm text-amber-700">Add medicines to the catalog before creating procurement orders. <a href="{{ getDashboardMedicineRoute('create') }}" class="font-medium text-brand-600 underline">Add medicine →</a></p>
                            @else
                                <p class="mt-3 text-xs text-gray-500">An NDoH inventory batch is created for each line when the order is received.</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Registered supplier <span class="text-red-500">*</span></label>
                                <select name="supplier_id" id="supplier_id" x-model="supplierId" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                    <option value="">Select supplier...</option>
                                    <template x-for="option in availableSuppliers" :key="option.id">
                                        <option :value="option.id" x-text="option.label"></option>
                                    </template>
                                </select>
                                <p class="mt-1 text-xs text-gray-500" x-show="source === 'overseas'">Overseas orders use registered manufacturers from India or China (~80% of PNG medicine imports).</p>
                                @error('supplier_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Source <span class="text-red-500">*</span></label>
                                <div class="flex gap-4">
                                    @foreach(['overseas' => 'Overseas', 'local' => 'Local', 'donation' => 'Donation'] as $value => $label)
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="source" value="{{ $value }}" x-model="source" class="text-brand-600 focus:ring-brand-600">
                                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('source')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="order_date" class="block text-sm font-medium text-gray-700 mb-1">Order Date <span class="text-red-500">*</span></label>
                                <input type="date" name="order_date" id="order_date" x-model="orderDate" max="{{ now()->format('Y-m-d') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                @error('order_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" id="expected_delivery_date" x-model="expectedDeliveryDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                @error('expected_delivery_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="supplier_invoice" class="block text-sm font-medium text-gray-700 mb-1">Supplier Invoice #</label>
                                <input type="text" name="supplier_invoice" id="supplier_invoice" x-model="supplierInvoice" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                @error('supplier_invoice')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="invoice_amount_foreign" class="block text-sm font-medium text-gray-700 mb-1">Foreign invoice total</label>
                                <div class="flex gap-2">
                                    <input type="number" name="invoice_amount_foreign" id="invoice_amount_foreign" x-model="invoiceAmountForeign" step="0.01" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50" placeholder="From medicine costs">
                                    <input type="text" name="invoice_currency" id="invoice_currency" x-model="invoiceCurrency" maxlength="3" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 uppercase" placeholder="INR">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Filled from catalog unit costs × quantity. Edit if the supplier quote differs.</p>
                                @error('invoice_amount_foreign')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                @error('invoice_currency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="invoice_amount" class="block text-sm font-medium text-gray-700 mb-1">Invoice Amount (PGK)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">K</span>
                                    <input type="number" name="invoice_amount" id="invoice_amount" x-model="invoiceAmount" step="0.01" min="0" class="w-full pl-8 rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Converted Kina total sent with the order to NDoH Admin.</p>
                                @error('invoice_amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-currency-converter
                                    quantity-alpine="totalQuantity"
                                    default-currency="INR"
                                />
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

                        <div class="mt-6 flex gap-3">
                            <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Create Order</button>
                            <a href="{{ getDashboardOrderRoute('index') }}" class="btn-module-secondary">Cancel</a>
                        </div>
                    </form>
        </div>
    </x-page-container>

    <script>
        function createOrderForm(initial) {
            return {
                items: initial.items ?? [{ medicine_id: '', quantity_ordered: '' }],
                medicineOptions: initial.medicineOptions ?? [],
                supplierOptions: initial.supplierOptions ?? [],
                lmisSuggestions: initial.lmisSuggestions ?? {},
                supplierId: initial.supplierId ?? '',
                source: initial.source ?? 'overseas',
                orderDate: initial.orderDate ?? '',
                expectedDeliveryDate: initial.expectedDeliveryDate ?? '',
                supplierInvoice: initial.supplierInvoice ?? '',
                invoiceAmount: initial.invoiceAmount ?? '',
                invoiceAmountForeign: initial.invoiceAmountForeign ?? '',
                invoiceCurrency: initial.invoiceCurrency ?? '',
                notes: initial.notes ?? '',
                notesEdited: initial.notesEdited ?? false,
                foreignQuote: null,
                syncingForeignTotal: false,
                sourceLabels: {
                    overseas: 'Overseas',
                    local: 'Local',
                    donation: 'Donation',
                },
                suggestionFor(medicineId) {
                    if (!medicineId) {
                        return null;
                    }

                    return this.lmisSuggestions[String(medicineId)] ?? null;
                },
                applySuggestion(index) {
                    const suggestion = this.suggestionFor(this.items[index].medicine_id);
                    if (suggestion && Number(suggestion.suggested_quantity) > 0 && !this.items[index].quantity_ordered) {
                        this.items[index].quantity_ordered = String(suggestion.suggested_quantity);
                    }
                    this.applyPreferredSupplierFromItems();
                    this.refreshNotes();
                },
                useSuggestion(index) {
                    const suggestion = this.suggestionFor(this.items[index].medicine_id);
                    if (suggestion && Number(suggestion.suggested_quantity) > 0) {
                        this.items[index].quantity_ordered = String(suggestion.suggested_quantity);
                        this.refreshNotes();
                    }
                },
                get totalQuantity() {
                    return this.items.reduce((sum, item) => sum + (Number(item.quantity_ordered) || 0), 0);
                },
                get availableSuppliers() {
                    const allowed = {
                        overseas: ['india', 'china'],
                        local: ['png'],
                        donation: ['international'],
                    }[this.source] ?? [];

                    return this.supplierOptions.filter((option) => allowed.includes(option.country));
                },
                get supplierName() {
                    const option = this.supplierOptions.find((entry) => entry.id === String(this.supplierId));

                    return option ? option.name : '';
                },
                medicineFor(medicineId) {
                    return this.medicineOptions.find((entry) => entry.id === String(medicineId)) ?? null;
                },
                lineTotal(item) {
                    const medicine = this.medicineFor(item.medicine_id);
                    const qty = Number(item.quantity_ordered) || 0;
                    const unitCost = medicine?.unit_cost;

                    if (!medicine || unitCost === null || unitCost === undefined || qty <= 0) {
                        return null;
                    }

                    return qty * Number(unitCost);
                },
                lineCostLabel(item) {
                    const medicine = this.medicineFor(item.medicine_id);
                    if (!medicine) {
                        return 'Select a medicine';
                    }

                    if (medicine.unit_cost === null || medicine.unit_cost === undefined) {
                        return 'No catalog unit cost';
                    }

                    const currency = medicine.currency || '—';
                    const unit = Number(medicine.unit_cost).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 4,
                    });
                    const total = this.lineTotal(item);

                    if (total === null) {
                        return `${unit} ${currency} / ${medicine.unit || 'unit'}`;
                    }

                    const line = total.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });

                    return `${unit} ${currency} → ${line} ${currency}`;
                },
                get pricedLines() {
                    return this.items
                        .map((item) => {
                            const medicine = this.medicineFor(item.medicine_id);
                            const total = this.lineTotal(item);

                            if (!medicine || total === null || !medicine.currency) {
                                return null;
                            }

                            return { total, currency: medicine.currency };
                        })
                        .filter(Boolean);
                },
                get mixedCurrencies() {
                    const currencies = [...new Set(this.pricedLines.map((line) => line.currency))];

                    return currencies.length > 1;
                },
                get foreignTotal() {
                    if (this.mixedCurrencies || this.pricedLines.length === 0) {
                        return null;
                    }

                    return this.pricedLines.reduce((sum, line) => sum + line.total, 0);
                },
                get foreignTotalCurrency() {
                    if (this.mixedCurrencies || this.pricedLines.length === 0) {
                        const supplier = this.supplierOptions.find((entry) => entry.id === String(this.supplierId));

                        return supplier?.currency || this.invoiceCurrency || '';
                    }

                    return this.pricedLines[0].currency;
                },
                get foreignTotalLabel() {
                    if (this.mixedCurrencies) {
                        return 'Mixed currencies';
                    }

                    if (this.foreignTotal === null) {
                        return '—';
                    }

                    const amount = this.foreignTotal.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });

                    return `${amount} ${this.foreignTotalCurrency}`;
                },
                syncForeignQuoteFields() {
                    if (this.mixedCurrencies) {
                        return;
                    }

                    this.syncingForeignTotal = true;

                    if (this.foreignTotal !== null) {
                        this.invoiceAmountForeign = this.foreignTotal.toFixed(2);
                        this.invoiceCurrency = this.foreignTotalCurrency;
                    } else if (this.supplierId) {
                        const supplier = this.supplierOptions.find((entry) => entry.id === String(this.supplierId));
                        if (supplier?.currency && !this.invoiceCurrency) {
                            this.invoiceCurrency = supplier.currency;
                        }
                    }

                    this.$nextTick(() => {
                        this.syncingForeignTotal = false;
                        this.pushToConverter();
                    });
                },
                pushToConverter() {
                    const amount = Number(this.invoiceAmountForeign);
                    const from = String(this.invoiceCurrency || this.foreignTotalCurrency || '').toUpperCase();

                    if (!from || !Number.isFinite(amount) || amount <= 0) {
                        return;
                    }

                    this.$dispatch('currency-sync', { amount, from });
                },
                init() {
                    ['supplierId', 'source', 'orderDate', 'expectedDeliveryDate', 'supplierInvoice', 'invoiceAmount', 'invoiceAmountForeign', 'invoiceCurrency']
                        .forEach((field) => this.$watch(field, () => this.refreshNotes()));

                    this.$watch('source', () => {
                        if (!this.availableSuppliers.some((option) => option.id === String(this.supplierId))) {
                            this.supplierId = '';
                        }
                    });

                    this.$watch('supplierId', () => {
                        const supplier = this.supplierOptions.find((entry) => entry.id === String(this.supplierId));
                        if (supplier?.currency && !this.foreignTotal) {
                            this.invoiceCurrency = supplier.currency;
                            this.pushToConverter();
                        }
                    });

                    this.$watch('items', () => {
                        this.applyPreferredSupplierFromItems();
                        this.syncForeignQuoteFields();
                        this.refreshNotes();
                    }, { deep: true });

                    this.$watch('invoiceAmountForeign', () => {
                        if (!this.syncingForeignTotal) {
                            this.pushToConverter();
                        }
                    });

                    this.$watch('invoiceCurrency', () => {
                        if (!this.syncingForeignTotal) {
                            this.pushToConverter();
                        }
                    });

                    if (!this.notesEdited) {
                        this.refreshNotes();
                    }

                    this.$watch('totalQuantity', () => {
                        this.$dispatch('currency-recalculate');
                    });

                    this.$nextTick(() => this.syncForeignQuoteFields());
                },
                addItem() {
                    this.items.push({ medicine_id: '', quantity_ordered: '' });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                applyPreferredSupplierFromItems() {
                    const firstWithSupplier = this.items.find((item) => item.medicine_id);
                    if (!firstWithSupplier) {
                        return;
                    }

                    const medicine = this.medicineOptions.find((entry) => entry.id === String(firstWithSupplier.medicine_id));
                    if (!medicine?.supplier_id) {
                        return;
                    }

                    const isAvailable = this.availableSuppliers.some((option) => option.id === medicine.supplier_id);
                    if (isAvailable && !this.supplierId) {
                        this.supplierId = medicine.supplier_id;
                    }
                },
                medicineLabel(medicineId) {
                    const option = this.medicineOptions.find((entry) => entry.id === String(medicineId));

                    return option ? option.label : null;
                },
                applyPgkAmount(detail) {
                    this.invoiceAmount = Number(detail.amount).toFixed(2);
                    this.foreignQuote = detail;
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
                    const lines = [];
                    const itemLines = this.items
                        .filter((item) => item.medicine_id && Number(item.quantity_ordered) > 0)
                        .map((item) => {
                            const label = this.medicineLabel(item.medicine_id);

                            return label ? `${Number(item.quantity_ordered).toLocaleString()} × ${label}` : null;
                        })
                        .filter(Boolean);

                    if (itemLines.length === 1) {
                        lines.push(`Procurement order for ${itemLines[0]}.`);
                    } else if (itemLines.length > 1) {
                        lines.push(`Multi-line procurement order (${itemLines.length} medicines): ${itemLines.join('; ')}.`);
                    }

                    if (this.supplierName) {
                        const sourceLabel = this.sourceLabels[this.source] ?? this.source;
                        lines.push(`Supplier: ${this.supplierName} (${sourceLabel} source).`);
                    }

                    const orderDate = this.formatDate(this.orderDate);
                    if (orderDate) {
                        let dateLine = `Order date: ${orderDate}.`;
                        const deliveryDate = this.formatDate(this.expectedDeliveryDate);
                        if (deliveryDate) {
                            dateLine += ` Expected delivery: ${deliveryDate}.`;
                        }
                        lines.push(dateLine);
                    }

                    const invoiceDetails = [];
                    if (this.supplierInvoice) {
                        const invoiceNumber = String(this.supplierInvoice).replace(/^#/, '');
                        invoiceDetails.push(`Invoice #${invoiceNumber}`);
                    }
                    if (this.invoiceAmountForeign !== '' && this.invoiceAmountForeign !== null && this.invoiceCurrency) {
                        const foreign = Number(this.invoiceAmountForeign);
                        if (!Number.isNaN(foreign)) {
                            invoiceDetails.push(`Supplier quote: ${foreign.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${String(this.invoiceCurrency).toUpperCase()}`);
                        }
                    } else if (this.foreignQuote) {
                        invoiceDetails.push(`Converted amount: ${Number(this.foreignQuote.originalAmount).toLocaleString()} ${this.foreignQuote.from}`);
                    }
                    if (this.invoiceAmount !== '' && this.invoiceAmount !== null) {
                        const amount = Number(this.invoiceAmount);
                        if (!Number.isNaN(amount)) {
                            invoiceDetails.push(`Amount: K ${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} PGK`);
                        }
                    }
                    if (invoiceDetails.length) {
                        lines.push(`${invoiceDetails.join('. ')}.`);
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
