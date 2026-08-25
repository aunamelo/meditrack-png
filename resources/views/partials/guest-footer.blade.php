<footer class="guest-portal-footer-bar" data-guest-inert>
    <div class="guest-portal-footer-inner">
        <p class="guest-portal-footer-copy">
            &copy; {{ date('Y') }} National Department of Health, Papua New Guinea. All rights reserved.
        </p>
        <nav class="guest-portal-footer-legal" aria-label="Legal">
            <a href="{{ route('guest.privacy') }}" class="guest-portal-footer-link">Privacy Policy</a>
            <span class="guest-portal-footer-sep" aria-hidden="true">·</span>
            <a href="{{ route('guest.terms') }}" class="guest-portal-footer-link">Terms of Use</a>
            <span class="guest-portal-footer-sep" aria-hidden="true">·</span>
            <a href="{{ route('guest.support') }}" class="guest-portal-footer-link">Contact Support</a>
        </nav>
    </div>
</footer>
