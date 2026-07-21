<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create New Order
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <nav class="flex mb-6" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li>
                                <a href="{{ getDashboardOrderRoute('index') }}" class="text-sm font-medium text-gray-700 hover:text-[#0f766e]">Orders</a>
                            </li>
                            <li><span class="text-sm text-gray-500"> / Create</span></li>
                        </ol>
                    </nav>

                    <form action="{{ getDashboardOrderRoute('store') }}" method="POST"
                          @pgk-applied="applyPgkAmount($event.detail)"
                          x-data="createOrderForm({
                              drugId: @js(old('drug_id', '')),
                              quantityOrdered: @js(old('quantity_ordered', '')),
                              supplier: @js(old('supplier', '')),
                              source: @js(old('source', 'overseas')),
                              orderDate: @js(old('order_date', now()->format('Y-m-d'))),
                              expectedDeliveryDate: @js(old('expected_delivery_date', '')),
                              supplierInvoice: @js(old('supplier_invoice', '')),
                              invoiceAmount: @js(old('invoice_amount', '')),
                              notes: @js(old('notes', '')),
                              notesEdited: @js(filled(old('notes'))),
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
                            <div>
                                <label for="drug_id" class="block text-sm font-medium text-gray-700 mb-1">Drug <span class="text-red-500">*</span></label>
                                <select name="drug_id" id="drug_id" x-model="drugId" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                    <option value="">Select a drug...</option>
                                    @forelse($drugs as $drug)
                                        <option value="{{ $drug->id }}">{{ $drug->drug_name }} ({{ $drug->dosage }})</option>
                                    @empty
                                        <option value="" disabled>No NDoH drug types on file</option>
                                    @endforelse
                                </select>
                                @if($drugs->isEmpty())
                                    <p class="mt-1 text-sm text-amber-700">Add a drug entry first so the system knows which drug types NDoH can order. <a href="{{ getDashboardDrugRoute('create') }}" class="font-medium text-[#0f766e] underline">Add drug entry →</a></p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500">Select the drug type to order from the supplier. Written-off or depleted batches can still be used as a reference. A new NDoH batch is created when the order is received.</p>
                                @endif
                                @error('drug_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="quantity_ordered" class="block text-sm font-medium text-gray-700 mb-1">Quantity Ordered <span class="text-red-500">*</span></label>
                                <input type="number" name="quantity_ordered" id="quantity_ordered" x-model="quantityOrdered" min="1" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('quantity_ordered')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <x-supplier-quote-picker />

                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                                <input type="text" name="supplier" id="supplier" x-model="supplier" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('supplier')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Source <span class="text-red-500">*</span></label>
                                <div class="flex gap-4">
                                    @foreach(['overseas' => 'Overseas', 'local' => 'Local', 'donation' => 'Donation'] as $value => $label)
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="source" value="{{ $value }}" x-model="source" class="text-[#0f766e] focus:ring-[#0f766e]">
                                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('source')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="order_date" class="block text-sm font-medium text-gray-700 mb-1">Order Date <span class="text-red-500">*</span></label>
                                <input type="date" name="order_date" id="order_date" x-model="orderDate" max="{{ now()->format('Y-m-d') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('order_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" id="expected_delivery_date" x-model="expectedDeliveryDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('expected_delivery_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="supplier_invoice" class="block text-sm font-medium text-gray-700 mb-1">Supplier Invoice #</label>
                                <input type="text" name="supplier_invoice" id="supplier_invoice" x-model="supplierInvoice" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                @error('supplier_invoice')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="invoice_amount" class="block text-sm font-medium text-gray-700 mb-1">Invoice Amount (PGK)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">K</span>
                                    <input type="number" name="invoice_amount" id="invoice_amount" x-model="invoiceAmount" step="0.01" min="0" class="w-full pl-8 rounded-md border-gray-300 shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Updates automatically when you convert a foreign quote below.</p>
                                @error('invoice_amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-currency-converter
                                    quantity-alpine="quantityOrdered"
                                    default-currency="USD"
                                />
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

                        <div class="mt-6 flex gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0f766e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0d5f59]">Create Order</button>
                            <a href="{{ getDashboardOrderRoute('index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function createOrderForm(initial) {
            return {
                drugId: initial.drugId ?? '',
                quantityOrdered: initial.quantityOrdered ?? '',
                supplier: initial.supplier ?? '',
                source: initial.source ?? 'overseas',
                orderDate: initial.orderDate ?? '',
                expectedDeliveryDate: initial.expectedDeliveryDate ?? '',
                supplierInvoice: initial.supplierInvoice ?? '',
                invoiceAmount: initial.invoiceAmount ?? '',
                notes: initial.notes ?? '',
                notesEdited: initial.notesEdited ?? false,
                foreignQuote: null,
                selectedQuote: null,
                sourceLabels: {
                    overseas: 'Overseas',
                    local: 'Local',
                    donation: 'Donation',
                },
                init() {
                    ['drugId', 'quantityOrdered', 'supplier', 'source', 'orderDate', 'expectedDeliveryDate', 'supplierInvoice', 'invoiceAmount']
                        .forEach((field) => this.$watch(field, () => this.refreshNotes()));

                    if (!this.notesEdited) {
                        this.refreshNotes();
                    }

                    this.$watch('quantityOrdered', () => {
                        this.$dispatch('currency-recalculate');
                    });
                },
                applyPgkAmount(detail) {
                    this.invoiceAmount = Number(detail.amount).toFixed(2);
                    this.foreignQuote = detail;
                    this.refreshNotes();
                },
                selectedDrugLabel() {
                    const select = document.getElementById('drug_id');
                    if (!select || !this.drugId) {
                        return null;
                    }

                    const option = select.querySelector(`option[value="${CSS.escape(String(this.drugId))}"]`);

                    return option ? option.textContent.trim() : null;
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
                    const drug = this.selectedDrugLabel();
                    const quantity = Number(this.quantityOrdered);

                    if (drug && quantity > 0) {
                        lines.push(`Procurement order for ${quantity.toLocaleString()} units of ${drug}.`);
                    } else if (drug) {
                        lines.push(`Procurement order for ${drug}.`);
                    }

                    if (this.supplier) {
                        const sourceLabel = this.sourceLabels[this.source] ?? this.source;
                        lines.push(`Supplier: ${this.supplier} (${sourceLabel} source).`);
                    }

                    if (this.selectedQuote) {
                        lines.push(`Quote selected: ${this.selectedQuote.supplier_name} — K ${Number(this.selectedQuote.total_pgk).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} total (${this.selectedQuote.quote_currency} ${Number(this.selectedQuote.unit_price).toLocaleString()} per unit).`);
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
                    if (this.foreignQuote) {
                        invoiceDetails.push(`Supplier quote: ${Number(this.foreignQuote.originalAmount).toLocaleString()} ${this.foreignQuote.from}`);
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
