@props([
    'title',
    'description' => null,
    'actionUrl' => null,
    'actionLabel' => null,
    'icon' => 'cube',
])

<div {{ $attributes->merge(['class' => 'module-hero']) }}>
    <div class="module-hero-pattern" aria-hidden="true"></div>
    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <div class="module-hero-icon">
                <x-dashboard.icon :name="$icon" class="h-6 w-6" />
            </div>
            <div>
                <h3 class="font-display text-xl font-bold tracking-tight text-white sm:text-2xl">{{ $title }}</h3>
                @if($description)
                    <p class="mt-1 max-w-2xl text-sm text-brand-50/90">{{ $description }}</p>
                @endif
            </div>
        </div>
        @if($actionUrl && $actionLabel)
            <a href="{{ $actionUrl }}" class="module-hero-action">
                <x-dashboard.icon name="plus" class="h-4 w-4" />
                {{ $actionLabel }}
            </a>
        @elseif(isset($action))
            <div class="shrink-0">{{ $action }}</div>
        @endif
    </div>
</div>
