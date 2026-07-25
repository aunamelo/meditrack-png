@props([
    'size' => 'md',
])

@php
    $sizeClass = match ($size) {
        'sm' => 'h-8 w-8',
        'md' => 'h-10 w-10',
        'lg' => 'h-16 w-16',
        'xl' => 'h-20 w-20',
        '2xl' => 'h-32 w-32',
        'hero' => 'h-36 w-36 sm:h-40 sm:w-40',
        default => $size,
    };
@endphp

<img
    src="{{ asset('images/ndoh.png') }}"
    alt="National Department of Health — Papua New Guinea"
    {{ $attributes->merge(['class' => "object-contain {$sizeClass}"]) }}
/>
