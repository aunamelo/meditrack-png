{{--
  Reusable QR scanner for receive / dispense / transfer forms.

  Usage (drop next to the batch fields):
      <x-qr-scanner />

  Optional props:
      title="Scan batch QR"
      hint="Points the camera at a MediTrack batch label."

  Defense notes:
  - Logic lives in resources/js/qr-scanner.js (Alpine.data('qrScanner')).
  - Entirely client-side: no extra API route. It fills #drug_id / #batch_no /
    #expiry (and aliases) already present on the form.
  - Camera requires a secure context (HTTPS or localhost).
--}}
@props([
    'title' => 'Scan batch QR',
    'hint' => 'Point the camera at a MediTrack batch QR label. Fields fill automatically on a successful scan.',
])

<div
    class="qr-scanner-root"
    x-data="qrScanner"
    {{ $attributes }}
>
    <div class="flex flex-wrap items-center gap-3">
        <button
            type="button"
            class="btn-module-secondary inline-flex items-center gap-2"
            @click="openScanner()"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 8h4v4H7V8zm6 6h4v4h-4v-4z"/>
            </svg>
            {{ $title }}
        </button>
        <p class="m-0 text-xs text-muted">{{ $hint }}</p>
    </div>

    <p
        x-show="success"
        x-cloak
        class="mt-2 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200"
        role="status"
        x-text="success"
    ></p>

    <p
        x-show="error && ! open"
        x-cloak
        class="mt-2 rounded border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300"
        role="alert"
        x-text="error"
    ></p>

    {{-- Modal overlay --}}
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        role="dialog"
        aria-modal="true"
        aria-label="QR scanner"
        @keydown.escape.window="closeScanner()"
    >
        <div
            class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-white/10 dark:bg-night-elevated"
            @click.outside="closeScanner()"
        >
            <div class="mb-3 flex items-start justify-between gap-3">
                <div>
                    <h3 class="m-0 font-display text-base font-bold text-[#132f4f] dark:text-zinc-50">{{ $title }}</h3>
                    <p class="mt-1 text-xs text-slate-600 dark:text-zinc-400">{{ $hint }}</p>
                </div>
                <button
                    type="button"
                    class="rounded p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                    @click="closeScanner()"
                    aria-label="Close scanner"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div
                x-show="error"
                x-cloak
                class="mb-3 rounded border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300"
                role="alert"
                x-text="error"
            ></div>

            {{-- html5-qrcode mounts the video feed into this element --}}
            <div
                :id="readerId"
                class="overflow-hidden rounded border border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-zinc-900"
                x-show="isSecureContext"
            ></div>

            <p
                x-show="scanning"
                x-cloak
                class="mt-2 text-center text-xs text-slate-500 dark:text-zinc-400"
            >
                Scanning… hold the label steady.
            </p>

            <div class="mt-4 flex justify-end">
                <button type="button" class="btn-module-secondary" @click="closeScanner()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
