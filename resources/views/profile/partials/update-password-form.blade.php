<section>
    <header class="mb-6 border-b border-line pb-4 dark:border-zinc-800">
        <h2 class="heading-section">Update password</h2>
        <p class="mt-1 text-sm text-muted">Use a strong password to protect your MediTrack PNG account.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="form-label">Current password</label>
            <input type="password" id="update_password_current_password" name="current_password" autocomplete="current-password" class="input-field">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="form-label">New password</label>
            <input type="password" id="update_password_password" name="password" autocomplete="new-password" class="input-field" minlength="10">
            <x-password-requirements />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="form-label">Confirm new password</label>
            <input type="password" id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password" class="input-field">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 border-t border-line pt-4 dark:border-zinc-800">
            <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Update password</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm text-emerald-600 dark:text-emerald-400"
                >Password updated.</p>
            @endif
        </div>
    </form>
</section>
