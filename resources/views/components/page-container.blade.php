@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'px-4 py-8 sm:px-6 lg:px-8']) }}>
    <div class="mx-auto max-w-7xl">
        {{ $slot }}
    </div>
</div>
