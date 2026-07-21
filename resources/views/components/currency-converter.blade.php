@props([
    'applyEvent' => 'pgk-applied',
    'applyLabel' => 'Use PGK amount',
    'perUnitEvent' => null,
    'perUnitLabel' => 'Use PGK per unit',
    'quantityAlpine' => null,
    'defaultCurrency' => 'USD',
    'autoApply' => true,
    'autoApplyTarget' => 'total',
])

<div
    x-data="currencyConverter({
        applyEvent: @js($applyEvent),
        perUnitEvent: @js($perUnitEvent),
        quantityAlpine: @js($quantityAlpine),
        defaultCurrency: @js($defaultCurrency),
        autoApply: @js($autoApply),
        autoApplyTarget: @js($autoApplyTarget),
        ratesUrl: @js(route('currency.rates')),
        convertUrl: @js(route('currency.convert')),
    })"
    x-init="init()"
    @currency-recalculate.window="if (Number(amount) > 0) convert()"
    {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-teal-200 bg-gradient-to-br from-teal-50/80 via-white to-emerald-50/50 shadow-sm']) }}
>
    {{-- Live rates header --}}
    <div class="border-b border-teal-100 bg-white/70 px-4 py-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <h4 class="text-sm font-semibold text-gray-900">Currency converter</h4>
                <span x-show="ratesSource === 'live'" x-cloak
                      class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-emerald-800">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    Live rates
                </span>
                <span x-show="ratesSource === 'fallback'" x-cloak
                      class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-amber-800">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    Offline estimate
                </span>
                <span x-show="loadingRates" x-cloak class="inline-flex items-center gap-1.5 text-[11px] text-gray-500">
                    <svg class="h-3.5 w-3.5 animate-spin text-[#0f766e]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Fetching rates…
                </span>
            </div>
            <div class="text-right text-[11px] text-gray-500" x-show="ratesUpdatedAt || currencyCount" x-cloak>
                <p x-show="ratesUpdatedAt">
                    Updated <span class="font-medium text-gray-700" x-text="formatRelativeTime(ratesUpdatedAt)"></span>
                </p>
                <p x-show="currencyCount" x-text="`${currencyCount} currencies tracked`"></p>
            </div>
        </div>
    </div>

    <div class="p-4">
        <p class="text-xs text-gray-600 mb-4">Convert supplier quotes to Kina (PGK) before recording costs. Rates refresh on each conversion.</p>

        {{-- Spot rate ticker --}}
        <div x-show="spotRate && fromCurrency" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mb-4 flex flex-wrap items-center gap-2 rounded-lg border border-teal-100 bg-white px-3 py-2.5 shadow-sm">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-[#0f766e]">Spot rate</span>
            <p class="text-sm font-medium text-gray-900">
                1 <span x-text="fromCurrency"></span>
                <span class="mx-1 text-gray-400">=</span>
                K <span x-text="formatMoney(spotRate, 4)"></span>
                <span class="text-gray-500">PGK</span>
            </p>
            <span x-show="loading" x-cloak class="ml-auto inline-flex items-center gap-1 text-[11px] text-[#0f766e]">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#0f766e] opacity-60"></span>
                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-[#0f766e]"></span>
                </span>
                Updating
            </span>
        </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="sm:col-span-1">
            <label class="block text-xs font-medium text-gray-700 mb-1">Amount</label>
            <input type="number" x-model="amount" min="0" step="0.01" placeholder="e.g. 10000"
                   @input.debounce.400ms="convert()"
                   class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
        </div>
        <div class="sm:col-span-1">
            <label class="block text-xs font-medium text-gray-700 mb-1">Currency</label>
            <select x-model="fromCurrency" @change="convert()"
                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[#0f766e] focus:ring focus:ring-[#0f766e] focus:ring-opacity-50">
                <template x-for="group in currencyGroups" :key="group.label">
                    <optgroup :label="group.label">
                        <template x-for="currency in group.items" :key="currency.code">
                            <option :value="currency.code" x-text="`${currency.code} — ${currency.label}`"></option>
                        </template>
                    </optgroup>
                </template>
            </select>
        </div>
        <div class="sm:col-span-1 flex items-end">
            <button type="button" @click="convert()" :disabled="loading"
                    class="w-full inline-flex justify-center items-center gap-2 px-3 py-2 bg-[#0f766e] text-white text-xs font-semibold uppercase rounded-md hover:bg-[#0d5f59] disabled:opacity-60 transition-colors">
                <svg x-show="loading" x-cloak class="h-3.5 w-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-show="!loading">Convert</span>
                <span x-show="loading" x-cloak>Live convert</span>
            </button>
        </div>
    </div>

    <div x-show="error" x-cloak class="mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600" x-text="error"></div>

    <div x-show="result || loading" x-cloak class="mt-4 space-y-3">
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-[#0f766e] to-[#0d5f59] text-white px-4 py-4 shadow-md"
             :class="{ 'opacity-90': loading }">
            <div x-show="loading" class="absolute inset-0 overflow-hidden">
                <div class="absolute inset-0 -translate-x-full animate-[shimmer_1.5s_infinite] bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs uppercase tracking-wide opacity-90">Converted to Kina</p>
                    <span x-show="!loading && ratesSource === 'live'" x-cloak class="inline-flex items-center gap-1 rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-medium">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                        Live
                    </span>
                </div>
                <p class="text-3xl font-bold mt-1 tracking-tight transition-all duration-300"
                   :class="justUpdated ? 'scale-[1.02]' : ''">
                    K <span x-text="result ? formatMoney(result.pgk) : '—'"></span>
                </p>
                <p x-show="result" class="text-sm mt-2 opacity-90">
                    <span x-text="formatMoney(amount)"></span>
                    <span x-text="fromCurrency"></span>
                    <span class="mx-1">→</span>
                    K <span x-text="formatMoney(result?.pgk)"></span> PGK
                </p>
                <p x-show="result?.per_unit_pgk" class="text-sm mt-2 opacity-90">
                    Per unit: K <span x-text="formatMoney(result?.per_unit_pgk, 4)"></span> PGK
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2" x-show="!autoApply" x-cloak>
            <button type="button" @click="applyPgk()"
                    class="inline-flex items-center px-3 py-1.5 bg-[#0f766e] text-white text-xs font-semibold uppercase rounded-md hover:bg-[#0d5f59]">
                {{ $applyLabel }}
            </button>
            @if($perUnitEvent)
                <button type="button" x-show="result?.per_unit_pgk" @click="applyPerUnit()"
                        class="inline-flex items-center px-3 py-1.5 bg-white border border-[#0f766e] text-[#0f766e] text-xs font-semibold uppercase rounded-md hover:bg-teal-50">
                    {{ $perUnitLabel }}
                </button>
            @endif
        </div>

        <p x-show="autoApply && applied" x-cloak class="flex items-center gap-2 text-xs text-teal-800">
            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
            </svg>
            PGK amount applied to the form automatically.
        </p>

        <div x-show="equivalentRows.length" x-cloak>
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-700">Same value across currencies</p>
                <span class="text-[10px] text-gray-400" x-show="ratesUpdatedAt" x-text="`Rates as of ${formatClockTime(ratesUpdatedAt)}`"></span>
            </div>
            <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-inner">
                <table class="min-w-full divide-y divide-gray-100 text-xs">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-500">Currency</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="row in equivalentRows" :key="row.code">
                            <tr :class="row.code === 'PGK' ? 'bg-teal-50 font-semibold' : 'hover:bg-gray-50 transition-colors'">
                                <td class="px-3 py-2 text-gray-700">
                                    <span x-text="row.code" class="font-medium"></span>
                                    <span class="text-gray-400" x-text="` · ${row.label}`"></span>
                                </td>
                                <td class="px-3 py-2 text-right text-gray-900 tabular-nums" x-text="formatCurrencyAmount(row.code, row.amount)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>

@once
    @push('scripts')
        <style>
            @keyframes shimmer {
                100% { transform: translateX(100%); }
            }
        </style>
        <script>
            function currencyConverter(config) {
                return {
                    amount: '',
                    fromCurrency: config.defaultCurrency || 'USD',
                    currencies: [],
                    currencyGroups: [],
                    equivalentRows: [],
                    result: null,
                    loading: false,
                    loadingRates: true,
                    error: '',
                    ratesSource: '',
                    ratesUpdatedAt: null,
                    currencyCount: 0,
                    spotRate: null,
                    justUpdated: false,
                    now: Date.now(),
                    ratesUrl: config.ratesUrl,
                    convertUrl: config.convertUrl,
                    applyEvent: config.applyEvent,
                    perUnitEvent: config.perUnitEvent,
                    quantityAlpine: config.quantityAlpine,
                    autoApply: config.autoApply !== false,
                    autoApplyTarget: config.autoApplyTarget || 'total',
                    applied: false,
                    async init() {
                        await this.loadCurrencies();

                        this.$watch('fromCurrency', () => {
                            this.refreshSpotRate();

                            if (Number(this.amount) > 0) {
                                this.convert();
                            }
                        });

                        setInterval(() => {
                            this.now = Date.now();
                        }, 15000);
                    },
                    pulseUpdated() {
                        this.justUpdated = true;
                        setTimeout(() => {
                            this.justUpdated = false;
                        }, 400);
                    },
                    async loadCurrencies() {
                        this.loadingRates = true;

                        try {
                            const response = await fetch(this.ratesUrl, {
                                headers: { 'Accept': 'application/json' },
                            });

                            if (!response.ok) {
                                throw new Error('Could not load currency list.');
                            }

                            const data = await response.json();
                            this.currencies = data.currencies || [];
                            this.ratesSource = data.rates_source || '';
                            this.ratesUpdatedAt = data.rates_updated_at || new Date().toISOString();
                            this.currencyCount = this.currencies.length;
                            this.currencyGroups = this.buildGroups(this.currencies);

                            if (!this.currencies.find((item) => item.code === this.fromCurrency)) {
                                this.fromCurrency = 'USD';
                            }

                            await this.refreshSpotRate();
                        } catch (exception) {
                            this.error = exception.message;
                        } finally {
                            this.loadingRates = false;
                        }
                    },
                    async refreshSpotRate() {
                        try {
                            const response = await fetch(`${this.convertUrl}?amount=1&from=${encodeURIComponent(this.fromCurrency)}`, {
                                headers: { 'Accept': 'application/json' },
                            });
                            const data = await response.json();

                            if (response.ok && data.pgk) {
                                this.spotRate = data.pgk;
                                this.ratesSource = data.rates_source || this.ratesSource;
                                this.ratesUpdatedAt = data.rates_updated_at || new Date().toISOString();
                            }
                        } catch (exception) {
                            // Spot rate is optional — ignore fetch errors here.
                        }
                    },
                    buildGroups(currencies) {
                        const common = currencies.filter((item) => item.common);
                        const other = currencies.filter((item) => !item.common);

                        return [
                            { label: 'Common procurement currencies', items: common },
                            { label: 'All currencies', items: other },
                        ].filter((group) => group.items.length > 0);
                    },
                    getQuantity() {
                        if (!this.quantityAlpine) {
                            return null;
                        }

                        let node = this.$el.parentElement;

                        while (node) {
                            const data = node._x_dataStack?.[0];

                            if (data && Object.prototype.hasOwnProperty.call(data, this.quantityAlpine)) {
                                const quantity = Number(data[this.quantityAlpine]);

                                return Number.isFinite(quantity) && quantity > 0 ? quantity : null;
                            }

                            node = node.parentElement;
                        }

                        return null;
                    },
                    async convert() {
                        this.error = '';

                        const amount = Number(this.amount);
                        if (!Number.isFinite(amount) || amount <= 0) {
                            this.result = null;
                            this.equivalentRows = [];
                            this.applied = false;

                            return;
                        }

                        this.loading = true;

                        try {
                            const params = new URLSearchParams({
                                amount: String(amount),
                                from: this.fromCurrency,
                            });

                            const quantity = this.getQuantity();
                            if (quantity) {
                                params.set('quantity', String(quantity));
                            }

                            const response = await fetch(`${this.convertUrl}?${params.toString()}`, {
                                headers: { 'Accept': 'application/json' },
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || 'Conversion failed.');
                            }

                            this.result = {
                                ...data,
                                from: this.fromCurrency,
                            };
                            this.ratesSource = data.rates_source || this.ratesSource;
                            this.ratesUpdatedAt = data.rates_updated_at || new Date().toISOString();
                            this.spotRate = Number(amount) > 0 ? data.pgk / Number(amount) : this.spotRate;
                            this.equivalentRows = Object.entries(data.equivalents || {})
                                .map(([code, value]) => ({
                                    code,
                                    label: (this.currencies.find((item) => item.code === code) || {}).label || code,
                                    amount: value,
                                }))
                                .sort((a, b) => a.code.localeCompare(b.code));

                            if (this.autoApply) {
                                this.applyAutomatically();
                            }

                            this.pulseUpdated();
                        } catch (exception) {
                            this.error = exception.message;
                            this.result = null;
                            this.equivalentRows = [];
                            this.applied = false;
                        } finally {
                            this.loading = false;
                        }
                    },
                    applyPgk() {
                        if (!this.result?.pgk) {
                            return;
                        }

                        this.$dispatch(this.applyEvent, {
                            amount: this.result.pgk,
                            from: this.fromCurrency,
                            originalAmount: Number(this.amount),
                        });
                        this.applied = true;
                    },
                    applyPerUnit() {
                        if (!this.result?.per_unit_pgk || !this.perUnitEvent) {
                            return;
                        }

                        this.$dispatch(this.perUnitEvent, {
                            amount: this.result.per_unit_pgk,
                            from: this.fromCurrency,
                        });
                        this.applied = true;
                    },
                    applyAutomatically() {
                        if (this.autoApplyTarget === 'per_unit' && this.perUnitEvent) {
                            if (this.result?.per_unit_pgk) {
                                this.applyPerUnit();
                            }

                            return;
                        }

                        if (this.result?.pgk) {
                            this.applyPgk();
                        }
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
                    formatCurrencyAmount(code, value) {
                        const zeroDecimal = ['JPY', 'KRW', 'IDR'];
                        const decimals = zeroDecimal.includes(code) ? 0 : 2;

                        if (code === 'PGK') {
                            return `K ${this.formatMoney(value, decimals)}`;
                        }

                        return `${this.formatMoney(value, decimals)} ${code}`;
                    },
                    formatRelativeTime(isoString) {
                        if (!isoString) {
                            return 'just now';
                        }

                        const then = new Date(isoString).getTime();
                        const seconds = Math.floor((this.now - then) / 1000);

                        if (seconds < 10) {
                            return 'just now';
                        }

                        if (seconds < 60) {
                            return `${seconds}s ago`;
                        }

                        const minutes = Math.floor(seconds / 60);

                        if (minutes < 60) {
                            return `${minutes}m ago`;
                        }

                        return this.formatClockTime(isoString);
                    },
                    formatClockTime(isoString) {
                        if (!isoString) {
                            return '';
                        }

                        return new Date(isoString).toLocaleTimeString(undefined, {
                            hour: 'numeric',
                            minute: '2-digit',
                        });
                    },
                };
            }
        </script>
    @endpush
@endonce
