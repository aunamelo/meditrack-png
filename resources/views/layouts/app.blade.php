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
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-surface-muted dark:bg-zinc-950 lg:pl-64">
            @auth
                @include('layouts.sidebar')
            @endauth

            <div class="flex min-h-screen flex-col">
                @auth
                    @include('layouts.topbar')
                @else
                    @include('layouts.navigation')
                @endauth

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
