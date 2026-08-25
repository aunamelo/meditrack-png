<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MediTrack PNG — National Department of Health medicine supply chain system.">
    <meta name="theme-color" content="#0f766e">
    <title>@yield('title', 'MediTrack PNG')</title>

    @include('partials.app-icon')

    @include('partials.theme-init')
    @include('partials.fonts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript>
        <style>.guest-portal-boot { display: none !important; }</style>
    </noscript>
    @stack('head')
</head>

@php
    $guestRoleKey = request('role') ? str_replace('-', '_', (string) request('role')) : null;
    $guestRoleMeta = $guestRoleKey ? config("portal.roles.{$guestRoleKey}") : null;
    $guestBrandIcon = $guestRoleMeta['brand_icon'] ?? 'images/ndoh-portal.png';
    $guestBrandAlt = $guestRoleMeta['brand_alt'] ?? 'National Department of Health — Papua New Guinea';
    $guestKicker = $guestRoleMeta['facility_group'] ?? 'National Department of Health';
    $guestTitle = $guestRoleMeta
        ? ($guestRoleMeta['brand_tagline'] ?? 'MediTrack PNG')
        : 'MediTrack PNG';
@endphp

<body class="guest-portal-body">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <div
        class="guest-portal-page"
        @if($guestRoleKey) data-role="{{ $guestRoleKey }}" @endif
        :class="{ 'guest-portal-page--sheet-open': signupOpen || langOpen }"
        x-data="{
            pageReady: {{ $errors->any() ? 'true' : 'false' }},
            signupOpen: false,
            langOpen: false,
            lang: 'en',
            toast: '',
            toastTimer: null,
            init() {
                if (this.pageReady) {
                    return;
                }
                const reveal = () => { this.pageReady = true; };
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    reveal();
                    return;
                }
                const start = () => {
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            setTimeout(reveal, 320);
                        });
                    });
                };
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', start, { once: true });
                } else {
                    start();
                }
                setTimeout(reveal, 1400);
            },
            selectLang(code) {
                this.langOpen = false;
                if (code === 'en') {
                    this.lang = 'en';
                    document.documentElement.lang = 'en';
                    return;
                }
                this.showToast('Coming soon');
            },
            showToast(message) {
                this.toast = message;
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => { this.toast = ''; }, 2800);
            },
        }"
    >
        <header class="guest-portal-topbar" data-guest-inert>
            <div class="guest-portal-topbar-inner">
                <a href="{{ route('home') }}" class="guest-portal-brand-link">
                    <img
                        src="{{ asset($guestBrandIcon) }}"
                        alt="{{ $guestBrandAlt }}"
                        class="guest-portal-topbar-logo"
                        width="80"
                        height="74"
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

                    <div class="guest-portal-lang" @click.outside="langOpen = false">
                        <button
                            type="button"
                            class="guest-portal-lang-btn"
                            @click="langOpen = ! langOpen; signupOpen = false"
                            :aria-expanded="langOpen.toString()"
                            aria-haspopup="dialog"
                            aria-controls="guest-lang-menu"
                            :aria-label="'Language: ' + (lang === 'en' ? 'English' : lang.toUpperCase())"
                        >
                            <span x-text="lang.toUpperCase()">EN</span>
                            <svg class="guest-portal-lang-caret" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div
                            id="guest-lang-menu"
                            class="guest-portal-lang-menu"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="guest-lang-title"
                            x-show="langOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 guest-portal-sheet--from"
                            x-transition:enter-end="opacity-100 guest-portal-sheet--to"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 guest-portal-sheet--to"
                            x-transition:leave-end="opacity-0 guest-portal-sheet--from"
                            @keydown.escape.window="langOpen = false"
                        >
                            <div class="guest-portal-lang-handle" aria-hidden="true"></div>
                            <div class="guest-portal-lang-heading">
                                <p id="guest-lang-title" class="guest-portal-lang-title">Language</p>
                                <button
                                    type="button"
                                    class="guest-portal-lang-close"
                                    @click="langOpen = false"
                                    aria-label="Close"
                                >
                                    Close
                                </button>
                            </div>
                            <ul class="guest-portal-lang-list" role="listbox" aria-labelledby="guest-lang-title">
                                <li role="option" :aria-selected="(lang === 'en').toString()">
                                    <button type="button" class="guest-portal-lang-option" :class="{ 'is-active': lang === 'en' }" @click="selectLang('en')">
                                        <span class="guest-portal-lang-code">EN</span>
                                        English
                                    </button>
                                </li>
                                <li role="option" aria-selected="false">
                                    <button type="button" class="guest-portal-lang-option" @click="selectLang('tp')">
                                        <span class="guest-portal-lang-code">TP</span>
                                        Tok Pisin
                                    </button>
                                </li>
                                <li role="option" aria-selected="false">
                                    <button type="button" class="guest-portal-lang-option" @click="selectLang('hm')">
                                        <span class="guest-portal-lang-code">HM</span>
                                        Hiri Motu
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="guest-portal-signup" @click.outside="signupOpen = false">
                        <button
                            type="button"
                            class="guest-portal-topnav-link is-cta"
                            @click="signupOpen = ! signupOpen; langOpen = false"
                            :aria-expanded="signupOpen.toString()"
                            aria-haspopup="dialog"
                            aria-controls="guest-signup-notice"
                        >
                            Request Access
                        </button>
                        <div
                            id="guest-signup-notice"
                            class="guest-portal-signup-panel"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="guest-signup-title"
                            aria-describedby="guest-signup-text"
                            x-show="signupOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 guest-portal-sheet--from"
                            x-transition:enter-end="opacity-100 guest-portal-sheet--to"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 guest-portal-sheet--to"
                            x-transition:leave-end="opacity-0 guest-portal-sheet--from"
                            @keydown.escape.window="signupOpen = false"
                        >
                            <div class="guest-portal-signup-handle" aria-hidden="true"></div>
                            <div class="guest-portal-signup-heading">
                                <span class="guest-portal-signup-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path stroke-linecap="round" d="M12 11v5"/>
                                        <circle cx="12" cy="8" r="0.75" fill="currentColor" stroke="none"/>
                                    </svg>
                                </span>
                                <p id="guest-signup-title" class="guest-portal-signup-title">Need an account?</p>
                                <button
                                    type="button"
                                    class="guest-portal-signup-close"
                                    @click="signupOpen = false"
                                    aria-label="Close"
                                >
                                    Close
                                </button>
                            </div>
                            <p id="guest-signup-text" class="guest-portal-signup-text">
                                Self-registration is not available. Contact your NDoH or facility administrator to request MediTrack access.
                            </p>
                        </div>
                    </div>
                </nav>
            </div>
        </header>

        <div
            class="guest-portal-signup-backdrop"
            x-show="signupOpen || langOpen"
            x-cloak
            x-transition.opacity.duration.200ms
            @click="signupOpen = false; langOpen = false"
            aria-hidden="true"
        ></div>

        <div
            class="guest-portal-toast"
            x-show="toast"
            x-cloak
            x-transition.opacity.duration.150ms
            role="status"
            aria-live="polite"
            x-text="toast"
        ></div>

        <main
            id="main-content"
            tabindex="-1"
            class="guest-portal-main outline-none"
            :aria-busy="(! pageReady).toString()"
        >
            <div
                class="guest-portal-boot"
                x-show="! pageReady"
                @if ($errors->any()) x-cloak @endif
                x-transition:leave="transition ease-out duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                role="status"
                aria-live="polite"
                aria-label="Loading MediTrack PNG"
            >
                <div class="guest-portal-boot-panel">
                    @if (request()->routeIs('home'))
                        <div class="guest-portal-boot-skeleton" aria-hidden="true">
                            <div class="guest-portal-boot-bone guest-portal-boot-bone--title"></div>
                            <div class="guest-portal-boot-bone guest-portal-boot-bone--lead"></div>
                            <div class="guest-portal-boot-bone guest-portal-boot-bone--lead-short"></div>
                            <div class="guest-portal-boot-cards">
                                @for ($i = 0; $i < 5; $i++)
                                    <div class="guest-portal-boot-card">
                                        <span class="guest-portal-boot-bone guest-portal-boot-bone--icon"></span>
                                        <span class="guest-portal-boot-card-lines">
                                            <span class="guest-portal-boot-bone guest-portal-boot-bone--label"></span>
                                            <span class="guest-portal-boot-bone guest-portal-boot-bone--sub"></span>
                                        </span>
                                    </div>
                                @endfor
                            </div>
                            <div class="guest-portal-boot-bone guest-portal-boot-bone--btn"></div>
                        </div>
                    @else
                        <div class="guest-portal-boot-spinner" aria-hidden="true"></div>
                    @endif
                </div>
            </div>
            <div class="guest-portal-main-inner">
                @yield('content')
            </div>
        </main>

        @include('partials.guest-footer')
    </div>

    @stack('scripts')
</body>

</html>
