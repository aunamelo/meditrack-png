@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'module-detail-card']) }}>
    @if($title)
        <div class="module-detail-card-header">
            <h3 class="text-sm font-semibold text-ink dark:text-zinc-100">{{ $title }}</h3>
            @if($subtitle)
                <p class="mt-0.5 text-xs text-muted">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div @class(['module-detail-card-body', 'pt-0' => ! $title])>
        {{ $slot }}
    </div>
</div>
