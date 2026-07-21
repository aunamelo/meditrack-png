@props(['tone' => 'amber', 'title', 'message', 'actionLabel' => null, 'actionUrl' => null, 'badge' => null, 'items' => []])

@php
$accent = match($tone) {
    'blue' => 'border-l-sky-500',
    default => 'border-l-amber-500',
};
$visibleItems = array_slice($items, 0, 3);
@endphp

<div class="surface-panel border-l-[3px] {{ $accent }}">
    <div class="px-5 py-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $title }}</h3>
                    @if($badge)
                        <span class="rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-semibold text-brand-700 dark:bg-brand-950 dark:text-brand-300">{{ $badge }} new</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-muted">{{ $message }}</p>
            </div>
            @if($actionLabel && $actionUrl)
                <a href="{{ $actionUrl }}" class="btn-brand shrink-0 px-3 py-2 text-xs">{{ $actionLabel }}</a>
            @endif
        </div>

        @if(count($visibleItems))
            <ul class="mt-4 space-y-2">
                @foreach($visibleItems as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="flex items-center justify-between gap-3 rounded-lg border border-line px-3 py-2.5 text-sm transition hover:border-brand-200 hover:bg-surface-muted dark:border-zinc-800 dark:hover:border-brand-800 dark:hover:bg-zinc-800/50">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-ink dark:text-zinc-100">{{ $item['title'] }}</p>
                                <p class="truncate text-xs text-muted">{{ $item['subtitle'] }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-medium text-brand-600 dark:text-brand-400">{{ $item['action'] ?? 'View' }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
