<section class="space-y-6">
    <header class="border-b border-line pb-4 dark:border-zinc-800">
        <h2 class="heading-section text-rose-700 dark:text-rose-300">Deactivate account</h2>
        <p class="mt-1 text-sm text-muted">
            Deactivate your portal login. Your name stays on historical orders, shipments, and dispensing records for audit. Contact an administrator if you need access restored.
        </p>
    </header>

    <button
        type="button"
        class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-rose-700 hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200 dark:hover:bg-rose-950/60"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Deactivate account</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-ink dark:text-zinc-100">
                {{ __('Deactivate your account?') }}
            </h2>

            <p class="mt-2 text-sm text-muted">
                {{ __('Enter your password to confirm. You will lose portal access immediately. Audit history is kept.') }}
            </p>

            <div class="mt-6">
                <label for="password" class="form-label">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="input-field mt-1"
                    placeholder="{{ __('Password') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="btn-module-secondary" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-rose-700">
                    {{ __('Deactivate account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
