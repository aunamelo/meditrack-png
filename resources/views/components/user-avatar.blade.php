@props([
    'user' => null,
    'size' => 'md',
    'rounded' => '2xl',
])

@php
    $user = $user ?? auth()->user();
    $sizeClasses = match ($size) {
        'sm' => 'h-8 w-8 text-xs rounded-lg',
        'lg' => 'h-20 w-20 text-2xl rounded-2xl',
        default => 'h-16 w-16 text-xl rounded-2xl',
    };
    $photoUrl = $user?->profilePhotoUrl();
@endphp

@if($photoUrl)
    <img
        {{ $attributes->merge(['class' => "{$sizeClasses} shrink-0 object-cover shadow-soft ring-2 ring-white/20"]) }}
        src="{{ $photoUrl }}"
        alt="{{ $user->name }}"
        loading="lazy"
    >
@else
    <div {{ $attributes->merge(['class' => "{$sizeClasses} flex shrink-0 items-center justify-center bg-brand-600 font-bold text-white shadow-soft"]) }}>
        {{ $user->initials() }}
    </div>
@endif
