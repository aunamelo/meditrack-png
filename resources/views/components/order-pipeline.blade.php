@props(['order'])

@php
    $stages = $order->pipelineStages();
    $progress = $order->pipelineProgressPercentage();
@endphp

<div {{ $attributes->merge(['class' => 'order-pipeline']) }}>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-section-label">Import pipeline</p>
            <h3 class="heading-section">Supply chain progress</h3>
            @if($order->requiresImportClearance())
                <p class="mt-1 text-sm text-muted">Overseas order — includes customs and FX clearance (typically 3–6 months).</p>
            @else
                <p class="mt-1 text-sm text-muted">{{ ucfirst($order->source) }} order — shorter domestic pipeline.</p>
            @endif
        </div>
        <div class="text-right">
            <p class="font-display text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $progress }}%</p>
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $order->statusLabel() }}</p>
        </div>
    </div>

    <div class="order-pipeline-track mb-6">
        <div class="order-pipeline-bar" style="width: {{ $progress }}%"></div>
    </div>

    <ol class="order-pipeline-steps">
        @foreach($stages as $stage)
            <li @class([
                'order-pipeline-step',
                'order-pipeline-step-completed' => $stage['completed'],
                'order-pipeline-step-current' => $stage['current'],
            ])>
                <div class="order-pipeline-marker" aria-hidden="true">
                    @if($stage['completed'])
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $stage['label'] }}</p>
                    @if($stage['date'])
                        <p class="text-xs text-muted">{{ $stage['date']->format('M d, Y') }}</p>
                    @elseif($stage['current'])
                        <p class="text-xs font-medium text-brand-600 dark:text-brand-400">In progress</p>
                    @else
                        <p class="text-xs text-muted">Pending</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</div>
