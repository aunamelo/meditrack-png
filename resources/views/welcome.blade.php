@extends('layouts.guest')

@section('title', 'MediTrack PNG | eLog Portal')

@php
    $portalRoles = config('portal.roles', []);
    $roleOrder = ['admin', 'procurement_officer', 'store_manager', 'pharmacy_manager', 'pharmacist'];
@endphp

@section('content')
    <form action="{{ route('login') }}" method="GET" id="roleSelectionForm" class="guest-portal-form-wrap">
        <div class="guest-portal-form">
            <div class="guest-portal-field">
                <label for="userRole" class="guest-portal-label">Your role</label>
                <div class="relative">
                    <select
                        id="userRole"
                        name="role"
                        required
                        class="guest-portal-input guest-portal-select appearance-none"
                    >
                        <option value="" disabled selected>Select role</option>
                        @foreach($roleOrder as $roleKey)
                            @if(isset($portalRoles[$roleKey]))
                                <option value="{{ str_replace('_', '-', $roleKey) }}">
                                    {{ $portalRoles[$roleKey]['label'] }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <span class="guest-portal-select-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                            <path d="M4.792 7.396L10 12.604l5.208-5.208" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
                <p id="roleError" class="guest-portal-error hidden" role="alert">Select a role to continue.</p>
            </div>

            <button type="submit" class="guest-portal-btn">
                Continue to sign in
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('roleSelectionForm').addEventListener('submit', function (event) {
            const select = document.getElementById('userRole');
            const error = document.getElementById('roleError');

            if (! select.value) {
                event.preventDefault();
                error.classList.remove('hidden');
                select.focus();
            } else {
                error.classList.add('hidden');
            }
        });
    </script>
@endpush
