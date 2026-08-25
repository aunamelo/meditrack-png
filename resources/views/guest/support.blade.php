@extends('layouts.guest')

@section('title', 'Contact Support | MediTrack PNG')

@section('content')
    <div class="guest-auth guest-legal">
        <a href="{{ route('home') }}" class="guest-auth-back">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to sign in
        </a>

        <h2 class="guest-auth-heading">Contact Support</h2>
        <p class="guest-auth-lead">
            MediTrack PNG is an authorized National Department of Health staff portal.
            Self-registration is not available.
        </p>

        <div class="guest-legal-body">
            <h3 class="guest-legal-subheading">Need an account?</h3>
            <p>
                Contact your NDoH or facility administrator to request MediTrack access for your role.
            </p>

            <h3 class="guest-legal-subheading">Sign-in or technical help</h3>
            <p>
                If you already have an account and cannot sign in, contact the NDoH ICT help desk through your
                facility administrator. Include your name, role, and facility so they can assist you.
            </p>

            <h3 class="guest-legal-subheading">Accessibility</h3>
            <p>
                For accessibility barriers, see the
                <a href="{{ route('guest.accessibility') }}" class="guest-auth-link">Accessibility</a>
                statement or report the issue to the NDoH ICT help desk.
            </p>
        </div>
    </div>
@endsection
