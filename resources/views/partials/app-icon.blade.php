@php
    $roleMeta = auth()->check() ? \App\Services\PortalNavigationService::currentRoleMeta() : null;
    $favicon = asset($roleMeta['brand_icon'] ?? 'images/ndoh.png');
    $faviconType = str_ends_with($favicon, '.webp') ? 'image/webp' : 'image/png';
@endphp
<link rel="icon" type="{{ $faviconType }}" href="{{ $favicon }}">
<link rel="apple-touch-icon" href="{{ $favicon }}">
