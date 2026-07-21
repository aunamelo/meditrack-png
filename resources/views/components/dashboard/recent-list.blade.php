@props(['items' => [], 'title' => 'Recent'])

<div class="surface-panel">
    <div class="border-b border-line px-5 py-3 dark:border-zinc-800">
        <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $title }}</h3>
    </div>
    @if(count($items))
        <ul class="divide-y divide-line dark:divide-zinc-800">
            @foreach($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="flex items-center justify-between gap-3 px-5 py-3 text-sm transition hover:bg-surface-muted dark:hover:bg-zinc-800/40">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-ink dark:text-zinc-100">{{ $item['title'] }}</p>
                            <p class="truncate text-xs text-muted">{{ $item['subtitle'] }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-ink-faint dark:text-zinc-500">{{ $item['meta'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p class="px-5 py-6 text-sm text-muted">No recent activity.</p>
    @endif
</div>
