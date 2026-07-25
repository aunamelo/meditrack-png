@php
    $roleMeta = $roleMeta ?? $user->portalRoleMeta();
@endphp

<div class="module-panel overflow-hidden">
    <div class="border-b border-line bg-brand-50/60 px-6 py-6 dark:border-zinc-800 dark:bg-brand-950/30">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <x-user-avatar :user="$user" size="lg" />
                <div>
                    <h3 class="heading-section text-ink dark:text-zinc-100">{{ $user->name }}</h3>
                    <p class="mt-1 text-sm text-muted">{{ $user->job_title ?: ($roleMeta['subtitle'] ?? 'MediTrack PNG portal user') }}</p>
                    @if($roleMeta)
                        <span class="badge-brand mt-2 inline-flex">{{ $roleMeta['label'] }}</span>
                    @endif
                </div>
            </div>
            <div class="text-sm text-muted">
                <p>Member since {{ $user->created_at->format('M d, Y') }}</p>
                @if($user->email_verified_at)
                    <p class="mt-1 text-emerald-700 dark:text-emerald-300">Email verified</p>
                @else
                    <p class="mt-1 text-amber-700 dark:text-amber-300">Email not verified</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
        <dl class="space-y-4">
            <x-module.detail-field label="Email" :value="$user->email" />
            <x-module.detail-field label="Phone" :value="$user->phone ?: 'Not provided'" />
            <x-module.detail-field label="Staff ID" :value="$user->employee_id ?: 'Not provided'" />
            <x-module.detail-field label="Work facility" :value="$user->facilityLabel()" />
        </dl>
        <dl class="space-y-4">
            @if($user->inventoryScopeLabel())
                <x-module.detail-field label="Inventory scope" :value="$user->inventoryScopeLabel()" />
            @endif
            <x-module.detail-field label="Portal access" :value="$user->roleLabel()" />
            <x-module.detail-field label="Last profile update" :value="$user->updated_at->format('M d, Y g:i A')" />
            <x-module.detail-field label="Account ID" :value="'#' . $user->id" />
        </dl>
    </div>

    @if(count($activitySummary ?? []))
        <div class="border-t border-line px-6 py-5 dark:border-zinc-800">
            <p class="text-section-label mb-3">Your workspace</p>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach($activitySummary as $item)
                    <div class="rounded-xl border border-line bg-surface-muted/60 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/60">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $item['label'] }}</p>
                        <p class="mt-1 font-display text-2xl font-bold text-ink dark:text-zinc-100">{{ $item['value'] }}</p>
                        <p class="mt-1 text-xs text-muted">{{ $item['hint'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
