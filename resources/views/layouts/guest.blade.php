<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MediTrack PNG eLog Portal — National Department of Health medicine supply chain system.">
    <meta name="theme-color" content="#0f766e">
    <title>@yield('title', 'MediTrack PNG | eLog Portal')</title>

    @include('partials.app-icon')

    @include('partials.theme-init')
    @include('partials.fonts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

@php
    $guestRoleKey = request('role') ? str_replace('-', '_', (string) request('role')) : null;
    $guestRoleMeta = $guestRoleKey ? config("portal.roles.{$guestRoleKey}") : null;
    $guestBrandIcon = $guestRoleMeta['brand_icon'] ?? 'images/ndoh-portal.png';
    $guestBrandAlt = $guestRoleMeta['brand_alt'] ?? 'National Department of Health — Papua New Guinea';
    $guestKicker = $guestRoleMeta['facility_group'] ?? 'National Department of Health';
    $guestTitle = $guestRoleMeta
        ? ($guestRoleMeta['brand_tagline'] ?? 'MediTrack PNG eLog Portal')
        : 'MediTrack PNG eLog Portal';
@endphp

<body class="guest-portal-body">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <div class="guest-portal-page" @if($guestRoleKey) data-role="{{ $guestRoleKey }}" @endif>
        <header class="guest-portal-topbar">
            <div class="guest-portal-topbar-inner">
                <a href="{{ route('home') }}" class="guest-portal-brand-link">
                    <img
                        src="{{ asset($guestBrandIcon) }}"
                        alt="{{ $guestBrandAlt }}"
                        class="guest-portal-topbar-logo"
                        width="56"
                        height="52"
                        decoding="async"
                        fetchpriority="high"
                    >
                    <div class="guest-portal-topbar-titles">
                        <p class="guest-portal-topbar-kicker">{{ $guestKicker }}</p>
                        <h1 class="guest-portal-topbar-title">{{ $guestTitle }}</h1>
                    </div>
                </a>

                <nav class="guest-portal-topnav" aria-label="Portal">
                    <a href="{{ route('home') }}" @class(['guest-portal-topnav-link', 'is-active' => request()->routeIs('home')])>Home</a>
                    <a href="{{ route('login') }}" @class(['guest-portal-topnav-link', 'is-active' => request()->routeIs('login')])>Sign in</a>
                    <div class="guest-portal-topnav-theme">
                        <x-theme-toggle compact />
                    </div>
                </nav>
            </div>
        </header>

        <main id="main-content" tabindex="-1" class="guest-portal-main outline-none">
            <div class="guest-portal-main-inner">
                @yield('content')
            </div>
        </main>

        @include('partials.guest-footer')
    </div>

    @stack('scripts')
</body>

</html>
