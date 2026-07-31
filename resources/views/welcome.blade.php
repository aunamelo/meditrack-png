@extends('layouts.guest')

@section('title', 'MediTrack PNG | eLog Portal')

@php
    $portalRoles = config('portal.roles', []);
    $roleOrder = ['admin', 'procurement_officer', 'store_manager', 'pharmacy_manager', 'pharmacist'];
@endphp

@section('content')
    <div class="guest-auth">
        <h2 class="guest-auth-heading">Choose your portal role</h2>
        <p class="guest-auth-lead">
            Select your MediTrack role to continue. If you do not have an account, contact your NDoH or facility administrator.
        </p>

        <form action="{{ route('login') }}" method="GET" id="roleSelectionForm" class="guest-auth-form">
            <div class="guest-auth-field">
                <label for="userRole" class="guest-auth-label">Your role</label>
                <select
                    id="userRole"
                    name="role"
                    required
                    class="guest-auth-input guest-auth-select"
                    aria-describedby="roleError"
                    aria-invalid="false"
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
                <p id="roleError" class="guest-auth-error hidden" role="alert" aria-live="assertive">Select a role to continue.</p>
            </div>

            <button type="submit" class="guest-auth-submit">
                Continue to sign in
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('roleSelectionForm').addEventListener('submit', function (event) {
            const select = document.getElementById('userRole');
            const error = document.getElementById('roleError');

            if (! select.value) {
                event.preventDefault();
                error.classList.remove('hidden');
                select.setAttribute('aria-invalid', 'true');
                select.focus();
            } else {
                error.classList.add('hidden');
                select.setAttribute('aria-invalid', 'false');
            }
        });
    </script>
@endpush
