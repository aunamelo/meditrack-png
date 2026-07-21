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

        @php
            // Role registry shared by the guest layout and the login form.
            // Add/edit roles here — both files read from the same source.
            $roles = [
                'admin' => [
                    'label'   => 'NDoH Admin',
                    'welcome' => 'Sign in to manage the meditrack system.',
                    'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>',
                ],
                'pharmacist' => [
                    'label'   => 'Pharmacist',
                    'welcome' => 'Sign in to manage medication records.',
                    'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>',
                ],
                'pharmacy-manager' => [
                    'label'   => 'Pharmacy Manager',
                    'welcome' => 'Sign in to oversee pharmacy operations.',
                    'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m-1 4h1m-1 4h1"></path>',
                ],
                'procurement-officer' => [
                    'label'   => 'Procurement Officer',
                    'welcome' => 'Sign in to manage supply chain activity.',
                    'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>',
                ],
                'store-manager' => [
                    'label'   => 'Store Manager',
                    'welcome' => 'Sign in to manage inventory control.',
                    'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>',
                ],
            ];

            $roleKey = request('role');
            $role    = $roles[$roleKey] ?? null;
        @endphp
    </head>
    <body class="font-sans antialiased bg-surface-muted dark:bg-zinc-950">
        <style>
            :root {
                --brand-50:  #eef7f7;
                --brand-100: #d7ecec;
                --brand-500: #0f766e;
                --brand-600: #0d5f59;
                --brand-700: #0a4b46;
                --ink-900:   #0f2027;
                --ink-700:   #33454b;
                --ink-500:   #64757b;
                --line:      #dfe7e6;
                --surface:   #ffffff;
                --surface-2: #f4f8f7;
                --bg:        #f4f8f7;
            }

            html.dark {
                --ink-900:   #f4f4f5;
                --ink-700:   #d4d4d8;
                --ink-500:   #a1a1aa;
                --line:      #3f3f46;
                --surface:   #18181b;
                --surface-2: #09090b;
                --bg:        #09090b;
            }

            body {
                margin: 0;
                min-height: 100vh;
                background: var(--bg);
                color: var(--ink-900);
                font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 2rem 1.5rem;
            }

            .auth-shell {
                width: 100%;
                max-width: 26rem;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .back-link {
                display: inline-flex;
                align-items: center;
                gap: .4rem;
                align-self: flex-start;
                font-size: .82rem;
                font-weight: 600;
                color: var(--ink-500);
                margin-bottom: 1.25rem;
                padding: .35rem .6rem .35rem .4rem;
                border-radius: 8px;
                transition: color .15s ease, background-color .15s ease;
            }

            .back-link:hover {
                color: var(--brand-600);
                background: var(--brand-50);
            }

            .back-link svg {
                width: 1rem;
                height: 1rem;
                flex-shrink: 0;
                transition: transform .15s ease;
            }

            .back-link:hover svg {
                transform: translateX(-2px);
            }

            .auth-logo {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 3.5rem;
                height: 3.5rem;
                border-radius: 14px;
                background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
                box-shadow: 0 8px 20px -8px rgba(15,118,110,.45);
                margin-bottom: 1.25rem;
            }

            .auth-logo svg { width: 1.75rem; height: 1.75rem; color: #fff; }

            @if ($role)
            .role-banner {
                width: 100%;
                display: flex;
                align-items: center;
                gap: .75rem;
                padding: .85rem 1rem;
                margin-bottom: 1rem;
                background: var(--brand-50);
                border: 1px solid var(--brand-100);
                border-radius: 12px;
            }

            .role-banner-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 2.25rem;
                height: 2.25rem;
                flex-shrink: 0;
                border-radius: 9px;
                background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
            }

            .role-banner-icon svg { width: 1.1rem; height: 1.1rem; color: #fff; }

            .role-banner-text .role-label {
                font-size: .85rem;
                font-weight: 600;
                color: var(--brand-700);
                line-height: 1.2;
            }

            .role-banner-text .role-sub {
                font-size: .75rem;
                color: var(--ink-500);
            }
            @endif

            .auth-card {
                width: 100%;
                background: var(--surface);
                border: 1px solid var(--line);
                border-radius: 18px;
                box-shadow: 0 1px 2px rgba(15,32,39,.04), 0 16px 40px -16px rgba(15,32,39,.14);
                padding: 1.75rem 1.75rem 2rem;
            }

            .auth-card label {
                display: block;
                font-size: .8rem;
                font-weight: 600;
                color: var(--ink-700);
                margin-bottom: .35rem;
            }

            .auth-card input[type="email"],
            .auth-card input[type="password"],
            .auth-card input[type="text"] {
                width: 100%;
                font-size: .9rem;
                color: var(--ink-900);
                background: var(--surface-2);
                border: 1px solid var(--line);
                border-radius: 10px;
                padding: .6rem .75rem;
                transition: border-color .15s ease, box-shadow .15s ease;
            }

            .auth-card input[type="email"]:focus,
            .auth-card input[type="password"]:focus,
            .auth-card input[type="text"]:focus {
                outline: none;
                border-color: var(--brand-500);
                box-shadow: 0 0 0 3px var(--brand-100);
                background: var(--surface);
            }

            .auth-card input[type="checkbox"] {
                accent-color: var(--brand-500);
            }

            .auth-card button[type="submit"],
            .auth-card .btn-primary {
                background: var(--brand-500);
                border: 1px solid var(--brand-600);
                color: #fff;
                font-size: .85rem;
                font-weight: 600;
                border-radius: 10px;
                padding: .55rem 1.1rem;
                transition: background-color .15s ease, transform .15s ease;
            }

            .auth-card button[type="submit"]:hover,
            .auth-card .btn-primary:hover {
                background: var(--brand-600);
            }

            .auth-card a {
                color: var(--brand-600);
            }

            .auth-card a:hover {
                color: var(--brand-700);
            }
        </style>

        <div class="auth-shell">
            <a href="{{ url('/') }}" class="back-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to portal selection
            </a>

            <a href="{{ url('/') }}" class="auth-logo">
                <x-application-logo class="w-7 h-7 fill-current text-white" />
            </a>

            @if ($role)
                <div class="role-banner">
                    <div class="role-banner-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $role['icon'] !!}</svg>
                    </div>
                    <div class="role-banner-text">
                        <div class="role-label">{{ $role['label'] }}</div>
                        <div class="role-sub">{{ $role['welcome'] }}</div>
                    </div>
                </div>
            @endif

            <div class="auth-card">
                {{ $slot }}
            </div>
        </div>

        <div class="fixed bottom-6 right-6 z-50">
            <x-theme-toggle />
        </div>
    </body>
</html>