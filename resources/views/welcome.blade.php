@extends('layouts.guest')

@section('title', 'MediTrack PNG')

@php
    $portalRoles = config('portal.roles', []);
    $roleOrder = ['admin', 'procurement_officer', 'store_manager', 'pharmacy_manager', 'pharmacist'];
    $roleIcons = [
        'admin' => 'shield',
        'procurement_officer' => 'clipboard',
        'store_manager' => 'warehouse',
        'pharmacy_manager' => 'hospital',
        'pharmacist' => 'pill',
    ];
    $roleLoginUrls = [];
    $roleLabels = [];
    $microsoftUrls = [];
    foreach ($roleOrder as $roleKey) {
        if (! isset($portalRoles[$roleKey])) {
            continue;
        }
        $roleSlug = str_replace('_', '-', $roleKey);
        $roleLoginUrls[$roleKey] = route('login', ['role' => $roleSlug]);
        $roleLabels[$roleKey] = $portalRoles[$roleKey]['label'];
        $microsoftUrls[$roleKey] = route('auth.microsoft.redirect', ['role' => $roleSlug]);
    }
    $oldRoleSlug = old('role');
    $oldRoleKey = $oldRoleSlug ? str_replace('-', '_', (string) $oldRoleSlug) : null;
    if ($oldRoleKey && ! isset($portalRoles[$oldRoleKey])) {
        $oldRoleKey = null;
    }
    $openLoginModal = $errors->any() && filled($oldRoleKey);
    $microsoftEnabled = \App\Services\PortalLoginService::microsoftConfigured();
@endphp

@section('content')
    <div
        class="guest-home flex min-h-[calc(100vh-200px)] flex-col items-center justify-center"
        @class(['guest-home--reopen-login' => $openLoginModal])
        x-data="guestRolePicker({
            selectedRole: @js($oldRoleKey),
            loginOpen: @js($openLoginModal),
            labels: @js($roleLabels),
            microsoftUrls: @js($microsoftUrls),
            email: @js(old('email', '')),
            emailError: @js($errors->first('email') ?: ''),
            passwordError: @js($errors->first('password') ?: ''),
            credentialsError: @js($errors->first('credentials') ?: ''),
        })"
    >
    <div class="guest-auth">
        <div class="guest-role-picker" x-show="! loginOpen" @if($openLoginModal) x-cloak @endif>
        <div class="guest-role-intro">
            <h2 id="guest-role-heading" class="guest-auth-heading guest-auth-heading--portal">Choose your portal role</h2>
            <p class="guest-auth-lead guest-auth-lead--portal">
                Select your MediTrack role to continue. If you do not have an account, contact your NDoH or facility administrator.
            </p>
        </div>

        <div
            class="guest-role-grid"
            role="radiogroup"
            aria-labelledby="guest-role-heading"
            :aria-invalid="showValidation.toString()"
            :class="{ 'guest-role-grid--attention': highlightGrid }"
        >
            @foreach($roleOrder as $roleKey)
                @if(isset($portalRoles[$roleKey]))
                    <a
                        href="{{ $roleLoginUrls[$roleKey] }}"
                        id="guest-role-card-{{ $roleKey }}"
                        class="guest-role-card guest-role-card--enter"
                        style="--guest-role-enter-delay: {{ ($loop->index) * 70 }}ms"
                        role="radio"
                        :aria-checked="selectedRole === @js($roleKey)"
                        :class="{ 'guest-role-card--selected': selectedRole === @js($roleKey) }"
                        @click.prevent="select(@js($roleKey), $event)"
                        @keydown.enter.prevent="select(@js($roleKey), $event)"
                        @keydown.space.prevent="select(@js($roleKey), $event)"
                    >
                        <span class="guest-role-card-icon" aria-hidden="true">
                            <x-dashboard.icon :name="$roleIcons[$roleKey]" class="h-6 w-6" />
                        </span>
                        <span class="guest-role-card-body">
                            <span class="guest-role-card-label">{{ $portalRoles[$roleKey]['label'] }}</span>
                            <span class="guest-role-card-subtitle">{{ $portalRoles[$roleKey]['subtitle'] }}</span>
                        </span>
                        <span
                            class="guest-role-card-check"
                            x-show="selectedRole === @js($roleKey)"
                            x-cloak
                            aria-hidden="true"
                        >
                            <x-dashboard.icon name="check-circle" class="h-5 w-5" />
                        </span>
                    </a>
                @endif
            @endforeach
        </div>

        <div class="guest-role-continue">
            {{-- Persistent live region: text updates on failed continue (DICT 11.8 / WCAG 2.0 AA). --}}
            <div
                id="guest-role-validation"
                class="guest-role-validation"
                role="status"
                aria-live="polite"
                aria-atomic="true"
            >
                <p
                    class="guest-role-validation-text"
                    :class="{ 'is-visible': showValidation }"
                    x-text="showValidation ? 'Please select a role to continue.' : ''"
                ></p>
            </div>

            <button
                type="button"
                class="guest-role-continue-btn"
                x-ref="continueBtn"
                :class="{
                    'is-enabled': selectedRole,
                    'is-shaking': shakeButton,
                }"
                :disabled="! selectedRole"
                :aria-disabled="(! selectedRole).toString()"
                :aria-describedby="showValidation ? 'guest-role-validation' : null"
                @click="continueToLogin()"
            >
                <span x-text="selectedRole ? ('Continue as ' + labels[selectedRole]) : 'Continue'"></span>
                <x-dashboard.icon name="arrow-right" class="guest-role-continue-arrow" />
            </button>

            <p class="guest-role-secure">
                <x-dashboard.icon name="lock" class="guest-role-secure-icon" />
                Secured connection — National Department of Health, Papua New Guinea
            </p>
        </div>
        </div>

        <template x-teleport="body">
            <div
                class="guest-login-modal"
                x-show="loginOpen"
                @unless($openLoginModal) x-cloak @endunless
                role="presentation"
                @keydown.escape.window="closeLogin()"
                @keydown.tab.window="trapFocus($event)"
            >
                <div
                    class="guest-login-modal-backdrop fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm"
                    x-show="loginOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="closeLogin()"
                    aria-hidden="true"
                ></div>

                <div
                    class="guest-login-modal-positioner fixed top-1/2 left-1/2 z-50 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 px-4"
                    x-show="loginOpen"
                    x-transition:enter="transition ease-out duration-250"
                    x-transition:enter-start="opacity-0 guest-login-modal-shell--from"
                    x-transition:enter-end="opacity-100 guest-login-modal-shell--to"
                    x-transition:leave="transition ease-in duration-180"
                    x-transition:leave-start="opacity-100 guest-login-modal-shell--to"
                    x-transition:leave-end="opacity-0 guest-login-modal-shell--from"
                    @click.stop
                >
                    <div
                        class="guest-login-modal-shell"
                        x-ref="loginPanel"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="modal-title"
                        :aria-describedby="credentialsError ? 'modal-description guest-auth-credentials-error' : 'modal-description'"
                        tabindex="-1"
                        :class="{ 'is-shaking': panelShaking }"
                    >
                        <button
                            type="button"
                            class="guest-login-modal-close"
                            @click="closeLogin()"
                            aria-label="Close modal"
                            data-tooltip="Close modal"
                        >
                            <x-dashboard.icon name="x" class="guest-login-modal-close-icon" />
                            <span class="guest-login-modal-close-label" aria-hidden="true">Close</span>
                        </button>

                        <div class="guest-login-modal-body">
                            <h2 id="modal-title" class="guest-auth-heading">Log in to an existing account</h2>
                            @include('auth.partials.login-error-banner')
                            <p id="modal-description" class="guest-auth-lead">
                                Signing in as <strong x-text="labels[selectedRole]"></strong>. No account? Contact your NDoH administrator.
                            </p>

                            @include('auth.partials.login-panel', [
                                'microsoftEnabled' => $microsoftEnabled,
                                'bindRole' => true,
                                'autofocusEmail' => false,
                            ])

                            <div class="guest-login-secure-wrap">
                                <p class="guest-login-secure">
                                    <svg class="guest-login-secure-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Secured connection — National Department of Health, Papua New Guinea
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    </div>
@endsection
