@props(['href', 'label' => 'Back'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'module-back-link']) }}>
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    {{ $label }}
</a>
