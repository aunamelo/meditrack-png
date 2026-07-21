@props([
    'quotesUrl' => route('procurement-officer.dashboard.orders.supplier-quotes'),
])

<div
    x-data="supplierQuotePicker({ quotesUrl: @js($quotesUrl) })"
    x-show="visible"
    x-cloak
    class="md:col-span-2 rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50/60 via-white to-violet-50/40 overflow-hidden"
>
    <div class="border-b border-indigo-100 bg-white/80 px-4 py-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h4 class="text-sm font-semibold text-gray-900">Compare supplier quotes</h4>
                <p class="text-xs text-gray-600 mt-0.5">Select the best offer for your budget — prices shown in Kina (PGK)</p>
            </div>
            <div class="flex items-center gap-2">
                <label for="budget_pgk" class="text-xs font-medium text-gray-700 whitespace-nowrap">Budget (PGK)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-xs text-gray-500">K</span>
                    <input type="number" id="budget_pgk" x-model="budgetPgk" min="0" step="0.01" placeholder="Optional"
                           @input.debounce.500ms="fetchQuotes()"
                           class="w-32 pl-6 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                </div>
            </div>
        </div>
    </div>

    <div class="p-4">
        <div x-show="loading" class="flex items-center justify-center gap-2 py-8 text-sm text-indigo-700">
            <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Loading supplier quotes…
        </div>

        <div x-show="!loading && quotes.length === 0" x-cloak class="py-6 text-center text-sm text-gray-500">
            No supplier quotes on file for this medicine yet.
        </div>

        <div x-show="!loading && quotes.length > 0" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <template x-for="quote in quotes" :key="quote.id">
                <button type="button"
                        @click="selectQuote(quote)"
                        class="text-left rounded-lg border-2 p-4 transition-all hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#0f766e] focus:ring-offset-2"
                        :class="{
                            'border-[#0f766e] bg-teal-50 ring-1 ring-[#0f766e]/20': selectedQuoteId === quote.id,
                            'border-gray-200 bg-white hover:border-indigo-300': selectedQuoteId !== quote.id,
                            'opacity-60': !quote.meets_minimum,
                        }">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-gray-900" x-text="quote.supplier_name"></p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <span x-text="quote.country"></span>
                                · <span x-text="quote.source.charAt(0).toUpperCase() + quote.source.slice(1)"></span>
                                <span x-show="quote.lead_time_days"> · <span x-text="quote.lead_time_days"></span> days delivery</span>
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <span x-show="quote.is_best_price" class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-800">Best price</span>
                            <span x-show="budgetPgk && quote.within_budget && quote.total_pgk > 0" class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase text-blue-800">Within budget</span>
                            <span x-show="budgetPgk && !quote.within_budget && quote.total_pgk > 0" class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase text-red-800">Over budget</span>
                        </div>
                    </div>

                    <div class="mt-3 flex items-end justify-between gap-3">
                        <div>
                            <p class="text-2xl font-bold text-[#0f766e]">
                                K <span x-text="formatMoney(quote.total_pgk)"></span>
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                                K <span x-text="formatMoney(quote.unit_price_pgk, 4)"></span>/unit
                                · <span x-text="formatMoney(quote.unit_price)"></span> <span x-text="quote.quote_currency"></span>/unit
                            </p>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wide"
                              :class="selectedQuoteId === quote.id ? 'text-[#0f766e]' : 'text-gray-400'"
                              x-text="selectedQuoteId === quote.id ? 'Selected ✓' : 'Select'"></span>
                    </div>

                    <p x-show="quote.notes" class="mt-2 text-xs text-gray-500 line-clamp-2" x-text="quote.notes"></p>
                    <p x-show="!quote.meets_minimum" class="mt-2 text-xs text-amber-700">
                        Min. order: <span x-text="quote.min_order_qty?.toLocaleString()"></span> units
                    </p>
                </button>
            </template>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function supplierQuotePicker(config) {
                return {
                    quotesUrl: config.quotesUrl,
                    visible: false,
                    loading: false,
                    quotes: [],
                    budgetPgk: '',
                    selectedQuoteId: null,
                    init() {
                        const form = this.$el.closest('form');

                        if (!form?._x_dataStack?.[0]) {
                            return;
                        }

                        const formData = form._x_dataStack[0];

                        formData.$watch('drugId', () => this.fetchQuotes());
                        formData.$watch('quantityOrdered', () => this.fetchQuotes());

                        if (formData.drugId && formData.quantityOrdered) {
                            this.fetchQuotes();
                        }
                    },
                    async fetchQuotes() {
                        const form = this.$el.closest('form');
                        const formData = form?._x_dataStack?.[0];

                        if (!formData?.drugId || !formData.quantityOrdered || Number(formData.quantityOrdered) < 1) {
                            this.visible = false;
                            this.quotes = [];

                            return;
                        }

                        this.visible = true;
                        this.loading = true;

                        try {
                            const params = new URLSearchParams({
                                drug_id: String(formData.drugId),
                                quantity: String(formData.quantityOrdered),
                            });

                            if (this.budgetPgk !== '' && Number(this.budgetPgk) > 0) {
                                params.set('budget', String(this.budgetPgk));
                            }

                            const response = await fetch(`${this.quotesUrl}?${params.toString()}`, {
                                headers: { 'Accept': 'application/json' },
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || 'Could not load quotes.');
                            }

                            this.quotes = data.quotes || [];

                            if (this.selectedQuoteId) {
                                const quote = this.quotes.find((item) => item.id === this.selectedQuoteId);

                                if (quote?.meets_minimum) {
                                    this.selectQuote(quote);
                                } else {
                                    this.selectedQuoteId = null;
                                }
                            }
                        } catch (exception) {
                            this.quotes = [];
                        } finally {
                            this.loading = false;
                        }
                    },
                    selectQuote(quote) {
                        if (!quote.meets_minimum) {
                            return;
                        }

                        const form = this.$el.closest('form');
                        const formData = form?._x_dataStack?.[0];

                        if (!formData) {
                            return;
                        }

                        this.selectedQuoteId = quote.id;
                        formData.supplier = quote.supplier_name;
                        formData.source = quote.source;
                        formData.invoiceAmount = Number(quote.total_pgk).toFixed(2);
                        formData.foreignQuote = {
                            amount: quote.total_pgk,
                            from: quote.quote_currency,
                            originalAmount: quote.unit_price * (Number(formData.quantityOrdered) || 1),
                        };
                        formData.selectedQuote = quote;
                        formData.notesEdited = false;
                        formData.refreshNotes();

                        formData.$dispatch('pgk-applied', {
                            amount: quote.total_pgk,
                            from: quote.quote_currency,
                            originalAmount: quote.unit_price * (Number(formData.quantityOrdered) || 1),
                        });
                    },
                    formatMoney(value, decimals = 2) {
                        const number = Number(value);
                        if (!Number.isFinite(number)) {
                            return '0.00';
                        }

                        return number.toLocaleString(undefined, {
                            minimumFractionDigits: decimals,
                            maximumFractionDigits: decimals,
                        });
                    },
                };
            }
        </script>
    @endpush
@endonce
