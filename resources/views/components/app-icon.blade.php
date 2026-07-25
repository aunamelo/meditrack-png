@props([
    'size' => 'md',
    'brand' => null,
])

@php
    $roleMeta = auth()->check() ? \App\Services\PortalNavigationService::currentRoleMeta() : null;

    if ($brand === 'modilon') {
        $icon = 'images/modilon-hospital.webp';
        $alt = 'Modilon General Hospital — Madang';
    } elseif ($brand === 'ndoh') {
        $icon = 'images/ndoh.png';
        $alt = 'National Department of Health — Papua New Guinea';
    } else {
        $icon = $roleMeta['brand_icon'] ?? 'images/ndoh.png';
        $alt = $roleMeta['brand_alt'] ?? 'National Department of Health — Papua New Guinea';
    }

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
    src="{{ asset($icon) }}"
    alt="{{ $alt }}"
    {{ $attributes->merge(['class' => "object-contain {$sizeClass}"]) }}
/>
