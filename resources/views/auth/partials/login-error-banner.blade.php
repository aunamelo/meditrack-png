<div
    id="guest-auth-credentials-error"
    class="guest-auth-banner"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    x-show="credentialsError"
    x-cloak
>
    <p class="guest-auth-banner-text" x-text="credentialsError"></p>
    <button
        type="button"
        class="guest-auth-banner-dismiss"
        @click="dismissCredentialsError()"
        aria-label="Dismiss error"
    >
        <x-dashboard.icon name="x" class="h-4 w-4" />
    </button>
</div>
