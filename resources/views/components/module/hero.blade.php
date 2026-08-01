@props([
    'title' => null,
    'description' => null,
    'actionUrl' => null,
    'actionLabel' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'module-hero'.(! $title ? ' module-hero-compact' : '')]) }}>
    <div class="relative flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            @if($title)
                <h3 class="font-display text-lg font-semibold tracking-display text-ink dark:text-zinc-50">{{ $title }}</h3>
            @endif
            @if($description)
                <p @class([
                    'max-w-3xl text-sm text-ink-secondary dark:text-zinc-300',
                    'mt-0.5' => $title,
                ])>{{ $description }}</p>
            @endif
        </div>
        @if($actionUrl && $actionLabel)
            <a href="{{ $actionUrl }}" class="module-hero-action">
                {{ $actionLabel }}
            </a>
        @elseif(isset($action))
            <div class="shrink-0">{{ $action }}</div>
        @endif
    </div>
</div>
