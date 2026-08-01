@props(['items' => [], 'title' => 'Recent activity'])

<div class="medcare-panel flex h-full flex-col">
    <div class="border-b border-line px-3.5 py-2.5">
        <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $title }}</h3>
    </div>
    @if(count($items))
        <ul class="flex-1 divide-y divide-line">
            @foreach($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="dashboard-recent-item">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-ink dark:text-zinc-100">{{ $item['title'] }}</p>
                            <p class="mt-0.5 truncate text-[11px] text-muted">{{ $item['subtitle'] }}</p>
                        </div>
                        @if(! empty($item['meta']))
                            <span class="dashboard-status-pill shrink-0">{{ $item['meta'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <div class="flex flex-1 flex-col justify-center px-3.5 py-8">
            <p class="text-sm text-ink-secondary">No recent records.</p>
        </div>
    @endif
</div>
