<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Account</p>
            <h2 class="heading-page">My Profile</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <div class="mb-6">
            @include('profile.partials.profile-overview', ['user' => $user, 'roleMeta' => $roleMeta, 'activitySummary' => $activitySummary])
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="module-form-shell">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="space-y-6">
                <div class="module-form-shell">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="module-form-shell">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
