<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="MediTrack PNG eLog Portal — National Department of Health medicine supply chain system." />
    <title>@yield('title', 'MediTrack PNG | eLog Portal')</title>

    @include('partials.theme-init')
    @include('partials.fonts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="guest-portal-body">
    <div class="guest-portal-bg-glow" aria-hidden="true"></div>

    <header class="guest-portal-official-bar">
        <p>Papua New Guinea · National Department of Health</p>
    </header>

    <div class="guest-portal-shell">
        <div class="guest-portal-card">
            @hasSection('content-top')
                @yield('content-top')
            @endif

            @include('partials.guest-portal-header')

            <div class="guest-portal-divider" aria-hidden="true"></div>

            @yield('content')

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
