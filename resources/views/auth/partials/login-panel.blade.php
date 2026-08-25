@php
    $microsoftEnabled = $microsoftEnabled ?? false;
    $roleKey = $roleKey ?? null;
    $bindRole = $bindRole ?? false;
    $autofocusEmail = $autofocusEmail ?? false;
@endphp

@if (session('status'))
    <div class="guest-auth-notice" role="status" aria-live="polite">{{ session('status') }}</div>
@endif

<a
    @if ($bindRole)
        :href="microsoftUrls[selectedRole]"
    @else
        href="{{ route('auth.microsoft.redirect', array_filter(['role' => $roleKey])) }}"
    @endif
    class="guest-auth-m365"
    @if (! $microsoftEnabled) title="Configure MICROSOFT_CLIENT_ID / SECRET in .env to enable" @endif
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

<form
    action="{{ route('login') }}"
    method="POST"
    class="guest-auth-form"
    novalidate
    @submit="onSubmit($event)"
    :aria-busy="isSubmitting.toString()"
>
    @csrf

    @if ($bindRole)
        <input type="hidden" name="role" :value="roleSlug()">
    @elseif ($roleKey)
        <input type="hidden" name="role" value="{{ $roleKey }}">
    @endif

    <p id="guest-auth-required-legend" class="guest-auth-legend">
        <span class="guest-auth-required" aria-hidden="true">*</span> Required field
    </p>

    <div class="guest-auth-field">
        <label for="email" class="guest-auth-label">
            Username or Email
            <span class="guest-auth-required" aria-hidden="true">*</span>
        </label>
        <input
            type="email"
            id="email"
            name="email"
            x-ref="loginEmail"
            x-model="email"
            required
            aria-required="true"
            @if ($autofocusEmail) autofocus @endif
            autocomplete="username"
            placeholder="Enter your username or email"
            class="guest-auth-input"
            :class="{ 'is-invalid': emailError || credentialsError }"
            :aria-invalid="(emailError || credentialsError) ? 'true' : 'false'"
            :aria-describedby="[
                'guest-auth-required-legend',
                emailError ? 'email-error' : null,
                credentialsError ? 'guest-auth-credentials-error' : null,
            ].filter(Boolean).join(' ')"
            @input="clearEmail()"
        />
        <p
            id="email-error"
            class="guest-auth-error"
            x-show="emailError"
            x-cloak
            x-text="emailError"
        ></p>
    </div>

    <div class="guest-auth-field">
        <label for="password" class="guest-auth-label">
            Password
            <span class="guest-auth-required" aria-hidden="true">*</span>
        </label>
        <div class="relative">
            <input
                :type="showPassword ? 'text' : 'password'"
                id="password"
                name="password"
                x-model="password"
                required
                aria-required="true"
                autocomplete="current-password"
                placeholder="Enter your password"
                class="guest-auth-input pe-11"
                :class="{ 'is-invalid': passwordError || credentialsError }"
                :aria-invalid="(passwordError || credentialsError) ? 'true' : 'false'"
                :aria-describedby="[
                    'guest-auth-required-legend',
                    passwordError ? 'password-error' : null,
                    credentialsError ? 'guest-auth-credentials-error' : null,
                ].filter(Boolean).join(' ')"
                @input="clearPassword()"
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
        <p
            id="password-error"
            class="guest-auth-error"
            x-show="passwordError"
            x-cloak
            x-text="passwordError"
        ></p>
    </div>

    @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}" class="guest-auth-link">Forgot your password?</a>
    @endif

    <label class="guest-auth-check">
        <input type="checkbox" name="remember" class="h-4 w-4 shrink-0 rounded border-slate-400 text-health-700 focus:ring-health-600" :disabled="isSubmitting">
        <span>Remember Me</span>
    </label>

    <button
        type="submit"
        class="guest-auth-submit"
        :class="{ 'is-loading': isSubmitting }"
        :disabled="isSubmitting"
        :aria-disabled="isSubmitting.toString()"
        :aria-busy="isSubmitting.toString()"
    >
        <svg
            x-show="isSubmitting"
            x-cloak
            class="guest-auth-spinner mr-2 h-4 w-4 animate-spin"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
        </svg>
        <span x-text="isSubmitting ? 'Signing in...' : 'Sign In'">Sign In</span>
    </button>
</form>
