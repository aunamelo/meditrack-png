@props(['items' => [], 'title' => 'Recent activity'])

<div class="medcare-panel flex h-full flex-col">
    <div class="border-b border-line/80 px-5 py-4">
        <p class="text-[11px] font-bold uppercase tracking-widest text-accent">Today</p>
        <h3 class="mt-1 font-display text-base font-bold text-ink">{{ $title }}</h3>
    </div>
    @if(count($items))
        <ul class="flex-1 divide-y divide-line/80">
            @foreach($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="flex items-start gap-3 px-5 py-4 transition hover:bg-canvas/80">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-dashboard.icon name="clipboard" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-ink">{{ $item['title'] }}</p>
                            <p class="mt-0.5 truncate text-xs text-muted">{{ $item['subtitle'] }}</p>
                        </div>
                        @if(! empty($item['meta']))
                            <span class="shrink-0 rounded-full bg-canvas px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-ink-muted">{{ $item['meta'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <div class="flex flex-1 flex-col items-center justify-center px-5 py-12 text-center">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-canvas">
                <x-dashboard.icon name="clipboard" class="h-5 w-5 text-ink-faint" />
            </div>
            <p class="text-sm font-medium text-ink-secondary">No recent activity</p>
            <p class="mt-1 text-xs text-muted">Updates will appear here as you work.</p>
        </div>
    @endif
</div>
