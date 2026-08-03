<footer class="guest-portal-footer-bar">
    <div class="guest-portal-footer-inner">
        <div class="guest-portal-footer-copy-block">
            <nav class="guest-portal-footer-legal" aria-label="Legal">
                <a href="{{ route('guest.accessibility') }}" class="guest-portal-footer-link">Accessibility</a>
                <span class="guest-portal-footer-sep" aria-hidden="true">|</span>
                <a href="{{ route('guest.privacy') }}" class="guest-portal-footer-link">Privacy Statement</a>
                <span class="guest-portal-footer-sep" aria-hidden="true">|</span>
                <a href="{{ route('guest.terms') }}" class="guest-portal-footer-link">Terms of Use</a>
            </nav>
            <p class="guest-portal-footer-copy">
                Copyright &copy; {{ date('Y') }} Papua New Guinea ·
                <a href="https://www.health.gov.pg/" class="guest-portal-footer-link" target="_blank" rel="noopener noreferrer">
                    National Department of Health
                </a>
                · MediTrack PNG
            </p>
            <p class="guest-portal-footer-help">
                Need access? Contact your facility administrator or NDoH ICT help desk.
            </p>
        </div>
        <p class="guest-portal-footer-note">Authorized NDoH personnel only</p>
    </div>
</footer>
