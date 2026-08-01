@props(['tone' => 'amber', 'title', 'message', 'actionLabel' => null, 'actionUrl' => null, 'badge' => null, 'items' => []])

@php
$toneStyles = match($tone) {
    'blue' => [
        'panel' => 'border-brand-600 border-y-line border-r-line dark:border-y-zinc-700 dark:border-r-zinc-700',
        'badge' => 'bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300',
    ],
    default => [
        'panel' => 'border-amber-600 border-y-line border-r-line dark:border-y-zinc-700 dark:border-r-zinc-700',
        'badge' => 'bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    ],
};
$visibleItems = array_slice($items, 0, 3);
@endphp

<div class="dashboard-alert {{ $toneStyles['panel'] }}">
    <div class="px-3.5 py-3 sm:px-4">
        <div class="flex flex-wrap items-start gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $title }}</h3>
                    @if($badge)
                        <span @class(['rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide', $toneStyles['badge']])>{{ $badge }}</span>
                    @endif
                </div>
                <p class="mt-0.5 text-sm text-muted">{{ $message }}</p>
            </div>
            @if($actionLabel && $actionUrl)
                <a href="{{ $actionUrl }}" class="btn-brand shrink-0 px-3 py-1.5 text-xs">{{ $actionLabel }}</a>
            @endif
        </div>

        @if(count($visibleItems))
            <ul class="mt-3 space-y-1.5">
                @foreach($visibleItems as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="dashboard-alert-item group">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-ink dark:text-zinc-100">{{ $item['title'] }}</p>
                                <p class="truncate text-[11px] text-muted">{{ $item['subtitle'] }}</p>
                            </div>
                            <span class="shrink-0 text-[11px] font-semibold text-health-700 group-hover:underline dark:text-health-300">
                                {{ $item['action'] ?? 'View' }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
