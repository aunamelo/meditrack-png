@props([
    'class' => 'mt-1 text-xs text-muted',
])

<p {{ $attributes->merge(['class' => $class]) }}>
    At least 10 characters, with uppercase, lowercase, a number, and a symbol (e.g. <span class="font-mono">!</span> <span class="font-mono">@</span> <span class="font-mono">#</span>).
</p>
