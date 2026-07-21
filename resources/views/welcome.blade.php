<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>MediTrack | Role Selection</title>

    @include('partials.theme-init')
    @include('partials.fonts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media (max-width: 767px) {
            .mob-height {
                height: 100vh;
                display: flex;
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .mob-height>.h-screen {
                height: auto !important;
            }
        }
    </style>
</head>

<body x-data="{ loaded: true }" class="bg-surface-muted font-sans antialiased dark:bg-zinc-950">

    <!-- ===== Preloader Start ===== -->
    <div x-show="loaded"
         x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 500) })"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed left-0 top-0 z-[999999] flex h-screen w-screen items-center justify-center bg-surface dark:bg-zinc-950">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="relative z-1 bg-surface-muted p-6 dark:bg-zinc-950 sm:p-0 mob-height">
        <div class="relative flex h-screen w-full flex-col justify-center dark:bg-zinc-950 sm:p-0 lg:flex-row">

            <!-- Form -->
            <div class="flex flex-col flex-1 w-full lg:w-1/2">
                <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                    <div>
                        <div class="mb-5 sm:mb-8">
                            <span class="badge-brand mb-4 inline-flex items-center gap-1.5 uppercase tracking-wide">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Secure Portal
                            </span>
                            <h1 class="heading-display mb-3 text-balance">
                                Welcome to MediTrack
                            </h1>
                            <p class="text-base font-medium text-muted">
                                Please select your role to continue
                            </p>
                        </div>

                        <div>
                            <form id="roleSelectionForm" action="javascript:void(0);" onsubmit="redirectToLogin(event)">
                                <div class="space-y-5">
                                    <!-- Role Selection -->
                                    <div>
                                        <label class="mb-2 block text-base font-semibold text-ink-secondary dark:text-zinc-300">
                                            Login as <span class="text-red-500">*</span>
                                        </label>
                                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                            <select id="userRole" name="userRole" required
                                                @change="isOptionSelected = $event.target.value !== ''"
                                                class="input-field z-20 h-12 w-full appearance-none bg-none py-3 pe-10 ps-4">
                                                <option value="" disabled selected>-- Login as --</option>
                                                <option value="admin">NDoH Admin</option>
                                                <option value="pharmacist">Pharmacist</option>
                                                <option value="pharmacy-manager">Pharmacy Manager</option>
                                                <option value="procurement-officer">Procurement Officer</option>
                                                <option value="store-manager">Store Manager</option>
                                            </select>
                                            <span class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400 pointer-events-none">
                                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </div>
                                        <p id="roleError" class="mt-1.5 hidden text-xs text-red-500">Please select a role to continue.</p>
                                    </div>

                                    <!-- Button -->
                                    <div>
                                        <button type="submit" class="btn-brand h-12 w-full text-base">
                                            Continue to Login
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Footer Section Start -->
                <style>
                    @media (max-width: 768px) {
                        .md\:flex-col { flex-direction: column; }
                    }
                    @media (max-width: 767px) {
                        .flex-col-reverse { flex-direction: column-reverse; }
                        footer .second-section { padding-right: 85px !important; }
                        .ft-pb-0 { padding-bottom: 0 !important; }
                    }
                </style>
                <footer class="border-t border-line bg-surface dark:border-zinc-800 dark:bg-zinc-950">
                    <div class="mx-auto md:px-6 py-4 ft-pb-0">
                        <div class="flex flex-col gap-3 items-center">
                            <div class="flex flex-wrap gap-4 md:gap-6 justify-center">
                                <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-[#0f766e] text-sm transition-colors">
                                    About MediTrack
                                </a>
                                <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-[#0f766e] text-sm transition-colors">
                                    User Guide
                                </a>
                                <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-[#0f766e] text-sm transition-colors">
                                    Support
                                </a>
                                <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-[#0f766e] text-sm transition-colors">
                                    Data Security
                                </a>
                                <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-[#0f766e] text-sm transition-colors">
                                    NDoH Portal
                                </a>
                            </div>
                            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                            <div class="text-gray-600 dark:text-gray-400 text-sm text-center second-section">
                                &copy; {{ date('Y') }} meditrack. All rights reserved.
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- Footer Section End -->
            </div>

            <!-- Visual / branding panel -->
            <div class="relative items-center hidden w-full h-full lg:grid lg:w-1/2"
                 style="background: radial-gradient(circle at 30% 20%, rgba(255,255,255,.14), transparent 55%), linear-gradient(160deg, #0d5f59 0%, #0a4b46 100%);">
                <div class="flex items-center justify-center z-1">
                    <div class="flex flex-col items-center max-w-sm text-center px-6">
                        <div class="flex items-center justify-center w-20 h-20 rounded-2xl bg-white/15 border border-white/25 mb-6">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <span class="text-4xl font-bold text-white tracking-tight mb-4">MediTrack PNG</span>
                        <p class="text-white/90 text-base leading-relaxed mb-6">
                            Papua New Guinea's National Health Information System
                        </p>
                        <div class="flex flex-col gap-3 w-full">
                            <div class="flex items-center gap-3 text-white/80 text-sm">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Medicine Management</span>
                            </div>
                            <div class="flex items-center gap-3 text-white/80 text-sm">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Supply Chain Tracking</span>
                            </div>
                            <div class="flex items-center gap-3 text-white/80 text-sm">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Healthcare Analytics</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dark mode toggle -->
            <div class="fixed bottom-6 right-6 z-50">
                <x-theme-toggle />
            </div>
        </div>
    </div>
    <!-- ===== Page Wrapper End ===== -->

    <script>
        function redirectToLogin(event) {
            event.preventDefault();
            const select = document.getElementById('userRole');
            const error = document.getElementById('roleError');
            const role = select.value;

            if (role) {
                error.classList.add('hidden');
                window.location.href = "{{ url('/login') }}?role=" + encodeURIComponent(role);
            } else {
                error.classList.remove('hidden');
            }
        }
    </script>
</body>

</html>