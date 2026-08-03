{{--
  Printable QR label for one inventory batch (Drug row).

  Usage: <x-drug-qr-label :drug="$drug" />

  Defense notes:
  - Payload is generated in PHP (Drug::qrPayloadJson) so the QR always
    matches the database record, not client-side JavaScript.
  - simplesoftwareio/simple-qrcode renders an SVG (no Imagick required).
  - "Print Label" uses window.print(); @media print CSS hides the rest of
    the page so only #drug-qr-label is printed.
--}}
@props(['drug'])

@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;

    $payload = $drug->qrPayloadJson();
    // SVG keeps the label crisp when printed and avoids needing the Imagick PHP extension.
    $qrSvg = QrCode::format('svg')->size(180)->margin(1)->generate($payload);
@endphp

<div class="drug-qr-label-panel rounded border border-line bg-surface p-4 dark:border-white/10 dark:bg-night-elevated">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2 print:hidden">
        <h3 class="m-0 font-display text-sm font-bold text-[#132f4f] dark:text-zinc-50">Batch QR label</h3>
        <button
            type="button"
            class="btn-module-secondary"
            onclick="window.print()"
        >
            Print Label
        </button>
    </div>

    {{-- Only this block is shown when printing --}}
    <div id="drug-qr-label" class="drug-qr-label flex flex-col items-center gap-4 sm:flex-row sm:items-start">
        <div class="drug-qr-label-code shrink-0 rounded border border-slate-200 bg-white p-2 dark:border-white/10">
            {!! $qrSvg !!}
        </div>
        <div class="drug-qr-label-meta min-w-0 text-sm">
            <p class="m-0 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500 dark:text-zinc-400">
                MediTrack PNG · Batch label
            </p>
            <p class="mt-1 font-display text-base font-bold text-[#132f4f] dark:text-zinc-50">
                {{ $drug->drug_name }}
            </p>
            <dl class="mt-2 space-y-1 text-slate-700 dark:text-zinc-300">
                <div class="flex flex-wrap gap-x-2">
                    <dt class="font-semibold text-slate-500 dark:text-zinc-400">Batch</dt>
                    <dd class="m-0 font-mono">{{ $drug->batch_number }}</dd>
                </div>
                <div class="flex flex-wrap gap-x-2">
                    <dt class="font-semibold text-slate-500 dark:text-zinc-400">Expiry</dt>
                    <dd class="m-0">{{ $drug->expiry_date->format('Y-m-d') }}</dd>
                </div>
                <div class="flex flex-wrap gap-x-2">
                    <dt class="font-semibold text-slate-500 dark:text-zinc-400">Drug ID</dt>
                    <dd class="m-0 font-mono">{{ $drug->id }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

@once
    {{-- Inline so we do not depend on a @stack('styles') in the layout. --}}
    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }
            #drug-qr-label,
            #drug-qr-label * {
                visibility: visible !important;
            }
            #drug-qr-label {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 1.5rem;
            }
            .print\:hidden {
                display: none !important;
            }
        }
    </style>
@endonce
