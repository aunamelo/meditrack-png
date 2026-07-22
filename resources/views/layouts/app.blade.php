<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MediTrack PNG') }}</title>

        @include('partials.theme-init')
        @include('partials.fonts')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
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
            class="flex h-screen min-h-screen overflow-hidden bg-surface dark:bg-zinc-950"
        >
            @auth
                @include('layouts.sidebar')
            @endauth

            <div class="flex h-screen min-h-screen min-w-0 flex-1 flex-col overflow-hidden bg-canvas dark:bg-zinc-950">
                @auth
                    @include('layouts.topbar')
                @else
                    @include('layouts.navigation')
                @endauth

                <main class="min-h-0 flex-1 overflow-x-hidden overflow-y-auto px-4 py-5 lg:px-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
