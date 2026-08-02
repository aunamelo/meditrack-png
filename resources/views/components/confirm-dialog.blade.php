<div
    x-data="confirmDialog"
    x-cloak
    x-show="open"
    class="fixed inset-0 z-[80] flex items-center justify-center p-4"
    role="presentation"
>
    <div
        class="absolute inset-0 bg-ink/40 dark:bg-black/60"
        @click="cancel()"
        aria-hidden="true"
    ></div>

    <div
        role="alertdialog"
        aria-modal="true"
        :aria-labelledby="'confirm-dialog-title'"
        :aria-describedby="'confirm-dialog-message'"
        class="relative w-full max-w-md rounded-xl border border-line bg-surface p-5 shadow-soft dark:border-zinc-700 dark:bg-zinc-900"
        @keydown.escape.window="if (open) cancel()"
    >
        <h2 id="confirm-dialog-title" class="font-sans text-base font-semibold text-ink dark:text-zinc-100" x-text="title"></h2>
        <p id="confirm-dialog-message" class="mt-2 text-sm text-ink-secondary dark:text-zinc-300" x-text="message"></p>

        <div class="mt-5 flex flex-wrap justify-end gap-2">
            <button
                type="button"
                class="btn-module-secondary"
                @click="cancel()"
            >
                Cancel
            </button>
            <button
                type="button"
                x-ref="confirmBtn"
                class="inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white"
                :class="danger ? 'bg-rose-600 hover:bg-rose-700' : 'bg-brand-600 hover:bg-brand-700'"
                @click="accept()"
                x-text="confirmLabel"
            ></button>
        </div>
    </div>
</div>
