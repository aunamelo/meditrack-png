@props(['items' => [], 'title' => 'Recent activity'])

<div class="surface-panel h-full">
    <div class="flex items-center justify-between border-b border-line px-5 py-4 dark:border-zinc-800">
        <div>
            <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $title }}</h3>
            <p class="mt-0.5 text-xs text-muted">Latest updates in your scope</p>
        </div>
        <x-dashboard.icon name="chart" class="h-4 w-4 text-brand-500" />
    </div>
    @if(count($items))
        <ul class="divide-y divide-line dark:divide-zinc-800">
            @foreach($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="dashboard-recent-item group">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-ink dark:text-zinc-100">{{ $item['title'] }}</p>
                            <p class="mt-0.5 truncate text-xs text-muted">{{ $item['subtitle'] }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if(! empty($item['meta']))
                                <span class="dashboard-status-pill">{{ $item['meta'] }}</span>
                            @endif
                            <svg class="h-4 w-4 text-ink-faint opacity-0 transition group-hover:opacity-100 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <div class="flex flex-col items-center justify-center px-5 py-12 text-center">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-surface-muted dark:bg-zinc-800">
                <x-dashboard.icon name="clipboard" class="h-5 w-5 text-ink-faint dark:text-zinc-500" />
            </div>
            <p class="text-sm font-medium text-ink-secondary dark:text-zinc-300">No recent activity</p>
            <p class="mt-1 text-xs text-muted">Updates will appear here as you work.</p>
        </div>
    @endif
</div>
