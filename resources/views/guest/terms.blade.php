@extends('layouts.guest')

@section('title', 'Terms of Use | MediTrack PNG')

@section('content')
    <div class="guest-auth guest-legal">
        <a href="{{ route('home') }}" class="guest-auth-back">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to sign in
        </a>

        <h2 class="guest-auth-heading">Terms of Use</h2>
        <p class="guest-auth-lead">
            These Terms of Use govern your use of the MediTrack PNG eLog portal.
            By using this portal, you agree to these terms. If you do not agree, you must not use it.
        </p>

        <div class="guest-legal-body">
            <h3 class="guest-legal-subheading">Proprietary rights</h3>
            <p>
                This portal is maintained for the National Department of Health of Papua New Guinea (NDoH) as an
                authorized staff e-service for medicine supply-chain operations.
            </p>

            <h3 class="guest-legal-subheading">Privacy</h3>
            <p>
                Please review the
                <a href="{{ route('guest.privacy') }}" class="guest-auth-link">Privacy Statement</a>
                for how personal and operational information is handled.
            </p>

            <h3 class="guest-legal-subheading">Restricted access</h3>
            <p>
                Access is restricted to authorized NDoH and facility personnel. You must keep your credentials confidential
                and report suspected unauthorized use to your administrator or the NDoH ICT help desk immediately.
                You are responsible for activity under your account arising from failure to keep credentials secure.
            </p>

            <h3 class="guest-legal-subheading">Acceptable use</h3>
            <p>
                You may use MediTrack only for authorized official duties. You must not attempt to access data or functions
                outside your role, interfere with the service, or misuse supply-chain or patient-related information.
            </p>

            <h3 class="guest-legal-subheading">Disclaimer and limitation of liability</h3>
            <p>
                While reasonable efforts are made to keep information accurate and the service available, NDoH does not
                guarantee completeness, uninterrupted availability, or that all material is always up to date.
                To the extent permitted by law, NDoH is not liable for loss or damage arising from use of, or inability
                to use, this portal.
            </p>

            <h3 class="guest-legal-subheading">External links</h3>
            <p>
                Links to external sites are provided for convenience. Use of those sites is at your own risk.
            </p>

            <h3 class="guest-legal-subheading">Security</h3>
            <p>
                Appropriate security technologies are used where practical. Internet communications may still be
                susceptible to interference. Take reasonable steps to keep your devices and credentials secure.
            </p>

            <h3 class="guest-legal-subheading">Updates and governing law</h3>
            <p>
                These Terms may be revised by updating this page. They are governed by the laws of the
                Independent State of Papua New Guinea.
            </p>
        </div>
    </div>
@endsection
