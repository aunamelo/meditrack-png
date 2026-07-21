@props(['tone' => 'amber', 'title', 'message', 'actionLabel' => null, 'actionUrl' => null, 'badge' => null, 'items' => []])

@php
$panelClasses = match($tone) {
    'blue' => 'border-blue-200 bg-blue-50 dark:border-blue-900/50 dark:bg-blue-950/30',
    default => 'border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-950/30',
};
$titleClasses = match($tone) {
    'blue' => 'text-blue-900 dark:text-blue-200',
    default => 'text-amber-900 dark:text-amber-200',
};
$messageClasses = match($tone) {
    'blue' => 'text-blue-800 dark:text-blue-300/90',
    default => 'text-amber-800 dark:text-amber-300/90',
};
$buttonClasses = match($tone) {
    'blue' => 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400',
    default => 'bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-400',
};
$dividerClasses = match($tone) {
    'blue' => 'divide-blue-200 border-blue-200 dark:divide-blue-900 dark:border-blue-900',
    default => 'divide-amber-200 border-amber-200 dark:divide-amber-900 dark:border-amber-900',
};
@endphp

<div class="overflow-hidden rounded-xl border shadow-soft {{ $panelClasses }}">
    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="font-display text-lg font-bold tracking-tight {{ $titleClasses }}">
                    {{ $title }}
                    @if($badge)
                        <span class="ml-2 inline-flex items-center rounded-full bg-white/70 px-2.5 py-0.5 text-xs font-semibold dark:bg-black/20">{{ $badge }} new</span>
                    @endif
                </h3>
                <p class="mt-1 text-sm font-medium {{ $messageClasses }}">{{ $message }}</p>
            </div>
            @if($actionLabel && $actionUrl)
                <a href="{{ $actionUrl }}" class="inline-flex shrink-0 items-center rounded-lg px-4 py-2 text-xs font-bold uppercase tracking-widest text-white {{ $buttonClasses }}">
                    {{ $actionLabel }}
                </a>
            @endif
        </div>

        @if(count($items))
            <ul class="mt-4 divide-y rounded-xl border bg-surface dark:bg-zinc-900 {{ $dividerClasses }}">
                @foreach($items as $item)
                    <li class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-ink dark:text-zinc-100">{{ $item['title'] }}</p>
                            <p class="truncate text-muted">{{ $item['subtitle'] }}</p>
                        </div>
                        <a href="{{ $item['url'] }}" class="shrink-0 font-semibold text-brand-600 hover:underline dark:text-brand-400">{{ $item['action'] ?? 'View' }} →</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
