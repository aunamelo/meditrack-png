@extends('layouts.guest')

@section('title', 'MediTrack PNG | Sign in')

@php
    $roleKey = request('role');
    $roleDbKey = $roleKey ? str_replace('-', '_', $roleKey) : null;
    $roleMeta = $roleDbKey ? config("portal.roles.{$roleDbKey}") : null;
    $microsoftEnabled = $microsoftEnabled ?? \App\Services\PortalLoginService::microsoftConfigured();
@endphp

@section('content')
    <div
        class="guest-auth"
        x-data="guestLoginForm({
            email: @js(old('email', '')),
            emailError: @js($errors->first('email') ?: ''),
            passwordError: @js($errors->first('password') ?: ''),
            credentialsError: @js($errors->first('credentials') ?: ''),
        })"
        :class="{ 'is-shaking': panelShaking }"
    >
        <a href="{{ route('home') }}" class="guest-auth-back">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to role selection
        </a>

        <h2 class="guest-auth-heading">Log in to an existing account</h2>
        @include('auth.partials.login-error-banner')
        <p class="guest-auth-lead">
            @if ($roleMeta)
                Signing in as <strong>{{ $roleMeta['label'] }}</strong>.
                If you do not have an account, please contact your NDoH or facility administrator.
            @else
                If you do not have an account, please contact your NDoH or facility administrator.
            @endif
        </p>

        @include('auth.partials.login-panel', [
            'microsoftEnabled' => $microsoftEnabled,
            'roleKey' => $roleKey,
            'bindRole' => false,
            'autofocusEmail' => true,
        ])
    </div>
@endsection
