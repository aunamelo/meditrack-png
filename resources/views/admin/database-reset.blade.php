<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Administration</p>
            <h2 class="heading-page">Database Reset</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <div class="space-y-6">
            <div class="module-panel p-6 border-2 border-rose-200 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/20">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-900 dark:text-rose-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-rose-900 dark:text-rose-100">Danger Zone</h3>
                        <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">
                            This action will permanently delete all data from the database except for users, medicines, and vehicles. This action cannot be undone.
                        </p>
                    </div>
                </div>
            </div>

            <div class="module-panel p-6">
                <h3 class="text-lg font-semibold text-ink mb-4">What will be deleted</h3>
                <ul class="space-y-2 text-sm text-muted">
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Drug inventory (all drugs and stock)
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Procurement orders
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Stock transfers
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Stock adjustments
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Patients
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Dispensing records
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Hospital orders and shipments
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Discrepancy reports
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Ward inventory (wards, issuances, returns, usages, stocks)
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Notifications
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        User sessions
                    </li>
                </ul>
            </div>

            <div class="module-panel p-6">
                <h3 class="text-lg font-semibold text-ink mb-4">What will be preserved</h3>
                <ul class="space-y-2 text-sm text-muted">
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        User accounts and roles
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Medicine catalogue
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Vehicles
                    </li>
                </ul>
            </div>

            <form action="{{ route('admin.dashboard.database-reset.reset') }}" method="POST" class="module-panel p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="confirmation" class="form-label">Type <code class="bg-surface-muted px-2 py-1 rounded text-sm">RESET_DATABASE</code> to confirm</label>
                        <input
                            type="text"
                            name="confirmation"
                            id="confirmation"
                            class="input-field"
                            placeholder="RESET_DATABASE"
                            required
                            autocomplete="off"
                        >
                        @error('confirmation')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="understand" required class="mt-1">
                        <label for="understand" class="text-sm text-muted">
                            I understand that this action cannot be undone and will permanently delete all data except users, medicines, and vehicles.
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('dashboard.admin') }}" class="btn-module-secondary">Cancel</a>
                        <button type="submit" class="btn-module-danger">Reset Database</button>
                    </div>
                </div>
            </form>
        </div>
    </x-page-container>
</x-app-layout>
