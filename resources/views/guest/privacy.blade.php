@extends('layouts.guest')

@section('title', 'Privacy Statement | MediTrack PNG')

@section('content')
    <div class="guest-auth guest-legal">
        <a href="{{ route('home') }}" class="guest-auth-back">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to sign in
        </a>

        <h2 class="guest-auth-heading">Privacy Statement</h2>
        <p class="guest-auth-lead">
            This is a National Department of Health (NDoH) MediTrack PNG portal.
            We are committed to protecting your privacy and providing a secure environment for authorized staff.
        </p>

        <div class="guest-legal-body">
            <h3 class="guest-legal-subheading">Information collection, use, and sharing</h3>
            <p>
                If you are only viewing the sign-in pages, we do not capture information that identifies you individually,
                beyond routine technical logs needed to operate and secure the service.
            </p>
            <p>
                When you sign in or use MediTrack, we may process account and operational data (such as your name, email,
                role, facility affiliation, and supply-chain transaction records) to deliver medicine logistics functions
                for NDoH and authorized facilities. Data may be shared with other authorized government bodies or
                service providers only where needed to deliver those functions, unless sharing is prohibited by law.
            </p>

            <h3 class="guest-legal-subheading">Security</h3>
            <p>
                Electronic storage and transmission of personal and operational data are protected with appropriate
                security controls. Authentication may use email and password and/or Microsoft 365 where configured.
            </p>

            <h3 class="guest-legal-subheading">Cookies</h3>
            <p>
                This portal may use session and preference cookies (for example, to keep you signed in and remember
                theme settings). These are used to operate the service, not for public advertising.
            </p>

            <h3 class="guest-legal-subheading">External websites</h3>
            <p>
                This portal may link to external sites (including
                <a href="https://www.health.gov.pg/" class="guest-auth-link" target="_blank" rel="noopener noreferrer">health.gov.pg</a>).
                Their privacy practices may differ from ours. We are not responsible for the content or privacy practices of external sites.
            </p>

            <h3 class="guest-legal-subheading">Contact</h3>
            <p>
                For privacy enquiries, or to request access to or correction of personal information held about you in MediTrack,
                contact your facility administrator or the NDoH ICT help desk.
            </p>

            <h3 class="guest-legal-subheading">Updates</h3>
            <p>
                This Privacy Statement may be updated from time to time. The current version will be published on this page.
            </p>
        </div>
    </div>
@endsection
