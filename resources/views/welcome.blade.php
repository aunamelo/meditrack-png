<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>MediTrack | Role Selection</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|inter:400,500,600,700" rel="stylesheet" />

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

<body x-data="{ loaded: true, darkMode: false }"
      x-init="darkMode = JSON.parse(localStorage.getItem('meditrack_dark') ?? 'false');
               $watch('darkMode', value => localStorage.setItem('meditrack_dark', JSON.stringify(value)))"
      :class="{ 'dark bg-gray-900': darkMode === true }"
      class="font-sans antialiased">

    <!-- ===== Preloader Start ===== -->
    <div x-show="loaded"
         x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 500) })"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed left-0 top-0 z-[999999] flex h-screen w-screen items-center justify-center bg-white dark:bg-gray-900">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-[#0f766e] border-t-transparent"></div>
    </div>
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0 mob-height">
        <div class="relative flex flex-col justify-center w-full h-screen dark:bg-gray-900 sm:p-0 lg:flex-row">

            <!-- Form -->
            <div class="flex flex-col flex-1 w-full lg:w-1/2">
                <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                    <div>
                        <div class="mb-5 sm:mb-8">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-[#d7ecec] bg-[#eef7f7] px-3 py-1.5 text-sm font-semibold uppercase tracking-wide text-[#0d5f59] mb-4">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Secure Portal
                            </span>
                            <h1 class="mb-3 font-bold text-gray-800 text-3xl dark:text-white/90">
                                Welcome to MediTrack
                            </h1>
                            <p class="text-base text-gray-500 dark:text-gray-400">
                                Please select your role to continue
                            </p>
                        </div>

                        <div>
                            <form id="roleSelectionForm" action="javascript:void(0);" onsubmit="redirectToLogin(event)">
                                <div class="space-y-5">
                                    <!-- Role Selection -->
                                    <div>
                                        <label class="mb-2 block text-base font-medium text-gray-700 dark:text-gray-400">
                                            Login as <span class="text-red-500">*</span>
                                        </label>
                                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                            <select id="userRole" name="userRole" required
                                                @change="isOptionSelected = $event.target.value !== ''"
                                                class="z-20 h-12 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-3 text-base text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-[#0f766e] focus:outline-none focus:ring-3 focus:ring-[#0f766e]/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-[#0f766e]"
                                                :class="isOptionSelected && 'text-gray-800 dark:text-white/90'">
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
                                        <button type="submit"
                                            class="flex items-center justify-center w-full px-4 py-3.5 text-base font-semibold text-white transition rounded-lg bg-[#0f766e] shadow-sm hover:bg-[#0d5f59]">
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
                <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
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
            <div class="fixed z-50 bottom-6 right-6">
                <button
                    class="inline-flex items-center justify-center text-white transition-colors rounded-full size-14 bg-[#0f766e] hover:bg-[#0d5f59]"
                    @click.prevent="darkMode = !darkMode">
                    <svg class="hidden fill-current dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM4.9029 5.9637C5.1958 6.25659 5.67067 6.25659 5.96356 5.9637C6.25646 5.6708 6.25646 5.19593 5.96356 4.90303L5.07968 4.01915C4.78679 3.72626 4.31191 3.72626 4.01902 4.01915C3.72613 4.31204 3.72613 4.78692 4.01902 5.07981L4.9029 5.9637Z" fill="" />
                    </svg>
                    <svg class="fill-current dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z" fill="" />
                    </svg>
                </button>
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