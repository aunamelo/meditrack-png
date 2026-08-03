@extends('layouts.guest')

@section('title', 'Accessibility | MediTrack PNG')

@section('content')
    <div class="guest-auth guest-legal">
        <a href="{{ route('home') }}" class="guest-auth-back">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to sign in
        </a>

        <h2 class="guest-auth-heading">Accessibility</h2>
        <p class="guest-auth-lead">
            MediTrack PNG aims to provide an accessible staff portal consistent with PNG Digital Transformation Policy
            e-Accessibility expectations (W3C WCAG 2.0 Level AA as the government target).
        </p>

        <div class="guest-legal-body">
            <h3 class="guest-legal-subheading">Change text size</h3>
            <p>
                You can enlarge or reduce text using your browser controls (for example, Ctrl + and Ctrl − on Windows,
                or Command + and Command − on macOS). Page layouts are designed to remain usable when text is resized.
            </p>

            <h3 class="guest-legal-subheading">Colour and contrast</h3>
            <p>
                Use the theme toggle in the top navigation to switch between light and dark presentation if that improves
                readability for you. We aim for sufficient contrast on interactive controls and primary text.
            </p>

            <h3 class="guest-legal-subheading">Keyboard and assistive technology</h3>
            <p>
                A “Skip to main content” link is available at the top of each page. Interactive elements such as role
                cards and form fields are intended to be reachable by keyboard. Where icons are decorative, they are
                hidden from assistive technology.
            </p>

            <h3 class="guest-legal-subheading">Documents and viewers</h3>
            <p>
                Some reports may be available as on-screen views, print layouts, or downloadable files (for example CSV).
                Use a current browser and, where needed, a PDF or spreadsheet viewer available for your operating system.
            </p>

            <h3 class="guest-legal-subheading">Accessibility level</h3>
            <p>
                We work toward WCAG 2.0 Level AA conformance for guest and authenticated portal pages. If you encounter
                a barrier, contact your facility administrator or the NDoH ICT help desk so we can improve the service.
            </p>
        </div>
    </div>
@endsection
