@props(['tone' => 'amber', 'title', 'message', 'actionLabel' => null, 'actionUrl' => null, 'badge' => null, 'items' => []])

@php
$toneStyles = match($tone) {
    'blue' => [
        'panel' => 'border-sky-200 bg-gradient-to-br from-sky-50/80 to-white dark:border-sky-900 dark:from-sky-950/30 dark:to-zinc-900',
        'icon' => 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
        'iconName' => 'truck',
    ],
    default => [
        'panel' => 'border-amber-200 bg-gradient-to-br from-amber-50/80 to-white dark:border-amber-900 dark:from-amber-950/30 dark:to-zinc-900',
        'icon' => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
        'iconName' => 'bell',
    ],
};
$visibleItems = array_slice($items, 0, 3);
@endphp

<div class="dashboard-alert {{ $toneStyles['panel'] }}">
    <div class="px-5 py-5 sm:px-6">
        <div class="flex flex-wrap items-start gap-4">
            <div @class(['dashboard-alert-icon shrink-0', $toneStyles['icon']])>
                <x-dashboard.icon :name="$toneStyles['iconName']" class="h-5 w-5" />
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-base font-semibold text-ink dark:text-zinc-100">{{ $title }}</h3>
                    @if($badge)
                        <span class="rounded-full bg-brand-600 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">{{ $badge }} new</span>
                    @endif
                </div>
                <p class="mt-1 text-sm leading-relaxed text-muted">{{ $message }}</p>
            </div>
            @if($actionLabel && $actionUrl)
                <a href="{{ $actionUrl }}" class="btn-brand shrink-0 px-4 py-2 text-xs shadow-sm">{{ $actionLabel }}</a>
            @endif
        </div>

        @if(count($visibleItems))
            <ul class="mt-5 space-y-2">
                @foreach($visibleItems as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="dashboard-alert-item group">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-ink dark:text-zinc-100">{{ $item['title'] }}</p>
                                <p class="truncate text-xs text-muted">{{ $item['subtitle'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950 dark:text-brand-300 dark:group-hover:bg-brand-500">
                                {{ $item['action'] ?? 'View' }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
