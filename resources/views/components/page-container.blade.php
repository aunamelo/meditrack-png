@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'pb-8']) }}>
    <div class="mx-auto max-w-[1400px]">
        {{ $slot }}
    </div>
</div>
