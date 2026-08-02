<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="MediTrack PNG — National Department of Health medicine supply chain tracking for Papua New Guinea.">
        <meta name="theme-color" content="#0f766e">

        <title>@isset($title){{ $title }} — @endisset MediTrack PNG</title>

        @include('partials.app-icon')

        @include('partials.theme-init')
        @include('partials.fonts')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-canvas font-sans antialiased text-ink dark:bg-night dark:text-zinc-100">
        <a href="#main-content" class="skip-link">Skip to main content</a>

        <div
            x-data="{
                sidebarOpen: false,
                sidebarCollapsed: false,
                init() {
                    this.sidebarCollapsed = localStorage.getItem('meditrack-sidebar-collapsed') === '1';
                },
                toggleSidebarCollapse() {
                    this.sidebarCollapsed = ! this.sidebarCollapsed;
                    localStorage.setItem('meditrack-sidebar-collapsed', this.sidebarCollapsed ? '1' : '0');
                },
            }"
            class="flex h-screen min-h-screen overflow-hidden bg-canvas dark:bg-night"
        >
            @auth
                @include('layouts.sidebar')
            @endauth

            <div class="flex h-screen min-h-screen min-w-0 flex-1 flex-col overflow-hidden bg-canvas dark:bg-night">
                @auth
                    @include('layouts.topbar')
                @else
                    @include('layouts.navigation')
                @endauth

                <main id="main-content" tabindex="-1" class="min-h-0 flex-1 overflow-x-hidden overflow-y-auto px-4 py-4 outline-none lg:px-6 lg:py-5">
                    {{ $slot }}
                </main>

                @auth
                    <footer class="app-portal-footer">
                        <div class="app-portal-footer-inner">
                            <p class="app-portal-footer-copy">
                                Copyright &copy; {{ date('Y') }} National Department of Health of Papua New Guinea · MediTrack eLog
                            </p>
                            <p class="app-portal-footer-note">Authorized NDoH personnel only</p>
                        </div>
                    </footer>
                @endauth
            </div>
        </div>
        <x-confirm-dialog />
        @stack('scripts')
    </body>
</html>
