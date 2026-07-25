@if($medicine->is_active)
    <form action="{{ getDashboardMedicineRoute('deactivate', $medicine) }}" method="POST" class="inline" onsubmit="return confirm('Mark this medicine inactive? It will be hidden from new procurement orders.');">
        @csrf
        <button type="submit" class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-amber-800 hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200 dark:hover:bg-amber-950/60">
            Mark inactive
        </button>
    </form>
@else
    <form action="{{ getDashboardMedicineRoute('activate', $medicine) }}" method="POST" class="inline">
        @csrf
        <button type="submit" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-emerald-800 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200 dark:hover:bg-emerald-950/60">
            Reactivate
        </button>
    </form>
@endif
