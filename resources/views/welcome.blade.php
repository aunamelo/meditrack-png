@extends('layouts.guest')

@section('title', 'MediTrack PNG')

@php
    $portalRoles = config('portal.roles', []);
    $roleOrder = ['admin', 'procurement_officer', 'store_manager', 'pharmacy_manager', 'pharmacist'];
    $roleIcons = [
        'admin' => 'shield',
        'procurement_officer' => 'clipboard',
        'store_manager' => 'warehouse',
        'pharmacy_manager' => 'hospital',
        'pharmacist' => 'pill',
    ];
    $facilityStats = [
        ['value' => '21', 'label' => 'Provincial Hospitals'],
        ['value' => '18', 'label' => 'District Hospitals'],
        ['value' => '737', 'label' => 'Health Centres'],
        ['value' => '49', 'label' => 'Community Health Posts'],
    ];
    $roleLoginUrls = [];
    $roleLabels = [];
    foreach ($roleOrder as $roleKey) {
        if (! isset($portalRoles[$roleKey])) {
            continue;
        }
        $roleLoginUrls[$roleKey] = route('login', ['role' => str_replace('_', '-', $roleKey)]);
        $roleLabels[$roleKey] = $portalRoles[$roleKey]['label'];
    }
@endphp

@section('content')
    <div
        class="guest-auth"
        x-data="{
            selectedRole: null,
            urls: @js($roleLoginUrls),
            labels: @js($roleLabels),
            showValidation: false,
            highlightGrid: false,
            shakeButton: false,
            select(role) {
                this.selectedRole = role;
                this.showValidation = false;
                this.highlightGrid = false;
            },
            continueToLogin() {
                if (! this.selectedRole) {
                    this.showValidation = true;
                    this.highlightGrid = true;
                    this.shakeButton = false;
                    this.$nextTick(() => {
                        this.shakeButton = true;
                        setTimeout(() => { this.shakeButton = false; }, 450);
                    });
                    return;
                }

                this.showValidation = false;
                this.highlightGrid = false;
                window.location.href = this.urls[this.selectedRole];
            },
        }"
    >
        <h2 id="guest-role-heading" class="guest-auth-heading">Choose your portal role</h2>
        <p class="guest-auth-lead">
            Select your MediTrack role to continue. If you do not have an account, contact your NDoH or facility administrator.
        </p>

        <div
            class="guest-role-grid"
            role="radiogroup"
            aria-labelledby="guest-role-heading"
            :aria-invalid="showValidation.toString()"
            :class="{ 'guest-role-grid--attention': highlightGrid }"
        >
            @foreach($roleOrder as $roleKey)
                @if(isset($portalRoles[$roleKey]))
                    <a
                        href="{{ $roleLoginUrls[$roleKey] }}"
                        class="guest-role-card guest-role-card--enter"
                        style="--guest-role-enter-delay: {{ ($loop->index) * 70 }}ms"
                        role="radio"
                        :aria-checked="selectedRole === @js($roleKey)"
                        :class="{ 'guest-role-card--selected': selectedRole === @js($roleKey) }"
                        @click.prevent="select(@js($roleKey))"
                        @keydown.enter.prevent="select(@js($roleKey))"
                        @keydown.space.prevent="select(@js($roleKey))"
                    >
                        <span class="guest-role-card-icon" aria-hidden="true">
                            <x-dashboard.icon :name="$roleIcons[$roleKey]" class="h-6 w-6" />
                        </span>
                        <span class="guest-role-card-body">
                            <span class="guest-role-card-label">{{ $portalRoles[$roleKey]['label'] }}</span>
                            <span class="guest-role-card-subtitle">{{ $portalRoles[$roleKey]['subtitle'] }}</span>
                        </span>
                        <span
                            class="guest-role-card-check"
                            x-show="selectedRole === @js($roleKey)"
                            x-cloak
                            aria-hidden="true"
                        >
                            <x-dashboard.icon name="check" class="h-5 w-5" />
                        </span>
                        <span
                            class="guest-role-card-chevron"
                            x-show="selectedRole !== @js($roleKey)"
                            aria-hidden="true"
                        >
                            <x-dashboard.icon name="chevron-right" class="h-5 w-5" />
                        </span>
                    </a>
                @endif
            @endforeach
        </div>

        <div class="guest-role-continue">
            {{-- Persistent live region: text updates on failed continue (DICT 11.8 / WCAG 2.0 AA). --}}
            <div
                id="guest-role-validation"
                class="guest-role-validation"
                role="status"
                aria-live="polite"
                aria-atomic="true"
            >
                <p
                    class="guest-role-validation-text"
                    :class="{ 'is-visible': showValidation }"
                    x-text="showValidation ? 'Please select a role to continue.' : ''"
                ></p>
            </div>

            <button
                type="button"
                class="guest-role-continue-btn"
                :class="{
                    'is-enabled': selectedRole,
                    'is-shaking': shakeButton,
                }"
                :aria-disabled="(! selectedRole).toString()"
                :aria-describedby="showValidation ? 'guest-role-validation' : null"
                @click="continueToLogin()"
            >
                <span x-text="selectedRole ? ('Continue as ' + labels[selectedRole]) : 'Continue'"></span>
            </button>
        </div>
    </div>

    <aside class="guest-facility-stats" aria-label="Papua New Guinea health facility network">
        <ul class="guest-facility-stats-grid">
            @foreach($facilityStats as $stat)
                <li class="guest-facility-stats-item">
                    <p class="guest-facility-stats-value">{{ $stat['value'] }}</p>
                    <span class="guest-facility-stats-rule" aria-hidden="true"></span>
                    <p class="guest-facility-stats-label">{{ $stat['label'] }}</p>
                </li>
            @endforeach
        </ul>
    </aside>
@endsection
