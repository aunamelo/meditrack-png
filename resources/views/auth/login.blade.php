@extends('layouts.guest')

@section('title', 'MediTrack PNG | Sign in')

@php
    $roleKey = request('role');
    $roleDbKey = $roleKey ? str_replace('-', '_', $roleKey) : null;
    $roleMeta = $roleDbKey ? config("portal.roles.{$roleDbKey}") : null;
    $microsoftEnabled = $microsoftEnabled ?? \App\Services\PortalLoginService::microsoftConfigured();
@endphp

@section('content')
    <div class="guest-auth">
        <a href="{{ route('home') }}" class="guest-auth-back">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to role selection
        </a>

        <h2 class="guest-auth-heading">Log in to an existing account</h2>
        <p class="guest-auth-lead">
            @if ($roleMeta)
                Signing in as <strong>{{ $roleMeta['label'] }}</strong>.
                If you do not have an account, please contact your NDoH or facility administrator.
            @else
                If you do not have an account, please contact your NDoH or facility administrator.
            @endif
        </p>

        @if (session('status'))
            <div class="guest-auth-notice" role="status" aria-live="polite">{{ session('status') }}</div>
        @endif

        <a
            href="{{ route('auth.microsoft.redirect', array_filter(['role' => $roleKey])) }}"
            class="guest-auth-m365"
            @if(! $microsoftEnabled) title="Configure MICROSOFT_CLIENT_ID / SECRET in .env to enable" @endif
        >
            <span class="guest-auth-m365-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 23" class="h-5 w-5" focusable="false">
                    <path fill="#f25022" d="M1 1h10v10H1z"/>
                    <path fill="#00a4ef" d="M12 1h10v10H12z"/>
                    <path fill="#7fba00" d="M1 12h10v10H1z"/>
                    <path fill="#ffb900" d="M12 12h10v10H12z"/>
                </svg>
            </span>
            Sign in with Microsoft 365
        </a>

        <div class="guest-auth-or" role="separator" aria-label="or">
            <span>or continue with email</span>
        </div>

        <form action="{{ route('login') }}" method="POST" class="guest-auth-form">
            @csrf

            @if ($roleKey)
                <input type="hidden" name="role" value="{{ $roleKey }}">
            @endif

            <div class="guest-auth-field">
                <label for="email" class="guest-auth-label">Login</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Username or email"
                    class="guest-auth-input"
                />
                @error('email')
                    <p class="guest-auth-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="guest-auth-field">
                <label for="password" class="guest-auth-label">Password</label>
                <div x-data="{ showPassword: false }" class="relative">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Password"
                        class="guest-auth-input pe-11"
                    />
                    <button
                        type="button"
                        @click="showPassword = ! showPassword"
                        class="guest-auth-eye"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    >
                        <svg x-show="! showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1 1 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="guest-auth-error">{{ $message }}</p>
                @enderror
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="guest-auth-link">Forgot your password?</a>
            @endif

            <label class="guest-auth-check">
                <input type="checkbox" name="remember" class="rounded border-slate-400 text-health-700 focus:ring-health-600">
                Remember Me
            </label>

            <button type="submit" class="guest-auth-submit guest-auth-submit-outline">
                Sign In
            </button>
        </form>
    </div>
@endsection
