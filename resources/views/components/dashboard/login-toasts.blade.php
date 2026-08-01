@if (session('login_success'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 4200)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-16 right-4 z-50 max-w-sm rounded border border-health-700 bg-health-700 px-3.5 py-2.5 text-white sm:right-6"
         role="status"
         aria-live="polite">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-white/70">Signed in</p>
        <p class="mt-0.5 text-sm font-semibold">{{ session('login_success') }}</p>
    </div>
@endif

@if (session('admin_pending_orders'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 8000)"
         class="fixed top-16 right-4 z-50 max-w-md rounded border border-amber-600 bg-amber-50 px-3.5 py-2.5 dark:bg-amber-950/40 sm:right-6 {{ session('login_success') ? 'mt-16' : '' }}"
         role="status"
         aria-live="polite">
        <p class="text-sm font-semibold text-amber-950 dark:text-amber-100">
            {{ session('admin_pending_orders') === 1 ? '1 order awaiting approval' : session('admin_pending_orders').' orders awaiting approval' }}
        </p>
        <p class="mt-0.5 text-xs text-amber-900/80 dark:text-amber-200/90">Review pending procurement on your dashboard.</p>
    </div>
@endif

@if (session('admin_pending_shipments'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 8000)"
         class="fixed top-16 right-4 z-50 max-w-md rounded border border-amber-600 bg-amber-50 px-3.5 py-2.5 dark:bg-amber-950/40 sm:right-6 {{ session('login_success') || session('admin_pending_orders') ? 'mt-16' : '' }}"
         role="status"
         aria-live="polite">
        <p class="text-sm font-semibold text-amber-950 dark:text-amber-100">
            {{ session('admin_pending_shipments') === 1 ? '1 shipment awaiting approval' : session('admin_pending_shipments').' shipments awaiting approval' }}
        </p>
        <p class="mt-0.5 text-xs text-amber-900/80 dark:text-amber-200/90">Approve NDoH → Lae AMS shipments before stock is sent.</p>
    </div>
@endif

@if (session('store_pending_shipments'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 8000)"
         class="fixed top-16 right-4 z-50 max-w-md rounded border border-brand-600 bg-brand-50 px-3.5 py-2.5 dark:bg-brand-950/40 sm:right-6 {{ session('login_success') ? 'mt-16' : '' }}"
         role="status"
         aria-live="polite">
        <p class="text-sm font-semibold text-brand-900 dark:text-brand-100">
            {{ session('store_pending_shipments') === 1 ? '1 incoming shipment from NDoH' : session('store_pending_shipments').' incoming shipments from NDoH' }}
        </p>
        <p class="mt-0.5 text-xs text-brand-800/80 dark:text-brand-200/90">Confirm receipt when the shipment arrives at Lae AMS.</p>
    </div>
@endif
