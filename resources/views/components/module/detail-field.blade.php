@props(['label', 'value' => null])

<div {{ $attributes }}>
    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $label }}</dt>
    <dd class="mt-1 text-sm font-medium text-ink dark:text-zinc-100">
        @if($value !== null)
            {{ $value }}
        @else
            {{ $slot }}
        @endif
    </dd>
</div>
