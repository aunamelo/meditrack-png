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
                <select
                    id="userRole"
                    name="role"
                    required
                    class="guest-portal-input guest-portal-select"
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
                <p id="roleError" class="guest-portal-error hidden" role="alert" aria-live="assertive">Select a role to continue.</p>
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
                select.setAttribute('aria-invalid', 'true');
                select.focus();
            } else {
                error.classList.add('hidden');
                select.setAttribute('aria-invalid', 'false');
            }
        });
    </script>
@endpush
