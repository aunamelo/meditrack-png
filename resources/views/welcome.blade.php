@extends('layouts.guest')

@section('title', 'MediTrack PNG | eLog Portal')

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
@endphp

@section('content')
    <div class="guest-auth">
        <h2 class="guest-auth-heading">Choose your portal role</h2>
        <p class="guest-auth-lead">
            Select your MediTrack role to continue. If you do not have an account, contact your NDoH or facility administrator.
        </p>

        <div class="guest-role-grid" role="list">
            @foreach($roleOrder as $roleKey)
                @if(isset($portalRoles[$roleKey]))
                    <a
                        href="{{ route('login', ['role' => str_replace('_', '-', $roleKey)]) }}"
                        class="guest-role-card"
                        role="listitem"
                    >
                        <span class="guest-role-card-icon" aria-hidden="true">
                            <x-dashboard.icon :name="$roleIcons[$roleKey]" class="h-6 w-6" />
                        </span>
                        <span class="guest-role-card-body">
                            <span class="guest-role-card-label">{{ $portalRoles[$roleKey]['label'] }}</span>
                            <span class="guest-role-card-subtitle">{{ $portalRoles[$roleKey]['subtitle'] }}</span>
                        </span>
                    </a>
                @endif
            @endforeach
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
