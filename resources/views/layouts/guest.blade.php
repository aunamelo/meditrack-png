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

<body class="guest-portal-body">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <div class="guest-portal-shell">
        <div class="guest-portal-card">
            @hasSection('content-top')
                @yield('content-top')
            @endif

            <header>
                @include('partials.guest-portal-header')
            </header>

            <div class="guest-portal-divider" aria-hidden="true"></div>

            <main id="main-content" tabindex="-1" class="outline-none">
                @yield('content')
            </main>

            @include('partials.guest-portal-trust')
        </div>

        @include('partials.guest-footer')
    </div>

    <div class="guest-portal-theme">
        <x-theme-toggle compact />
    </div>

    @stack('scripts')
</body>

</html>
