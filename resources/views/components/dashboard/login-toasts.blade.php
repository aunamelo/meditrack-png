@if (session('login_success'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 4200)"
         x-transition:leave="transition ease-in duration-500"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="fixed top-20 right-4 z-50 flex max-w-sm items-center gap-3 rounded-xl border border-health-800/20 bg-gradient-to-r from-health-700 via-health-700 to-brand-700 px-4 py-3.5 text-white shadow-lg sm:right-6"
         role="status"
         aria-live="polite">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/15">
            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/55">Signed in</p>
            <p class="mt-0.5 text-sm font-semibold text-white">{{ session('login_success') }}</p>
        </div>
    </div>
@endif

@if (session('admin_pending_orders'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 8000)"
         class="fixed top-20 right-4 z-50 flex max-w-md items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 shadow-lg dark:border-amber-900/50 dark:bg-amber-950/40 sm:right-6 {{ session('login_success') ? 'mt-20' : '' }}"
         role="status"
         aria-live="polite">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/50">
            <svg class="h-5 w-5 text-amber-700 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">
                {{ session('admin_pending_orders') === 1 ? '1 order awaiting approval' : session('admin_pending_orders').' orders awaiting approval' }}
            </p>
            <p class="mt-1 text-sm text-amber-800 dark:text-amber-200/90">Review pending procurement orders on your dashboard.</p>
        </div>
    </div>
@endif

@if (session('admin_pending_shipments'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 8000)"
         class="fixed top-20 right-4 z-50 flex max-w-md items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 shadow-lg dark:border-amber-900/50 dark:bg-amber-950/40 sm:right-6 {{ session('login_success') || session('admin_pending_orders') ? 'mt-20' : '' }}"
         role="status"
         aria-live="polite">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/50">
            <svg class="h-5 w-5 text-amber-700 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">
                {{ session('admin_pending_shipments') === 1 ? '1 shipment awaiting approval' : session('admin_pending_shipments').' shipments awaiting approval' }}
            </p>
            <p class="mt-1 text-sm text-amber-800 dark:text-amber-200/90">Approve NDoH → Lae AMS shipments before stock is sent.</p>
        </div>
    </div>
@endif

@if (session('store_pending_shipments'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 8000)"
         class="fixed top-20 right-4 z-50 flex max-w-md items-start gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3.5 shadow-lg dark:border-sky-900/50 dark:bg-sky-950/40 sm:right-6 {{ session('login_success') ? 'mt-20' : '' }}"
         role="status"
         aria-live="polite">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/50">
            <svg class="h-5 w-5 text-sky-700 dark:text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-sky-900 dark:text-sky-100">
                {{ session('store_pending_shipments') === 1 ? '1 incoming shipment from NDoH' : session('store_pending_shipments').' incoming shipments from NDoH' }}
            </p>
            <p class="mt-1 text-sm text-sky-800 dark:text-sky-200/90">Confirm receipt when the shipment arrives at Lae AMS.</p>
        </div>
    </div>
@endif
