<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>MediTrack | Login</title>

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

    @php
        // Role registry — keep in sync with resources/views/welcome.blade.php
        $roles = [
            'admin' => [
                'label'   => 'NDoH Admin',
                'welcome' => 'Enter your email and password to access system administration.',
            ],
            'pharmacist' => [
                'label'   => 'Pharmacist',
                'welcome' => 'Enter your email and password to access medication records.',
            ],
            'pharmacy-manager' => [
                'label'   => 'Pharmacy Manager',
                'welcome' => 'Enter your email and password to access pharmacy operations.',
            ],
            'procurement-officer' => [
                'label'   => 'Procurement Officer',
                'welcome' => 'Enter your email and password to access supply chain management.',
            ],
            'store-manager' => [
                'label'   => 'Store Manager',
                'welcome' => 'Enter your email and password to access inventory control.',
            ],
        ];

        $roleKey = request('role');
        $role    = $roles[$roleKey] ?? null;
    @endphp
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
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-[#0f766e] dark:text-gray-400 mb-6 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to portal selection
                        </a>

                        <div class="mb-5 sm:mb-8">
                            @if ($role)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-[#d7ecec] bg-[#eef7f7] px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-[#0d5f59] mb-3">
                                    {{ $role['label'] }}
                                </span>
                            @endif
                            <h1 class="mb-3 font-bold text-gray-800 text-3xl dark:text-white/90">
                                Sign In
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $role['welcome'] ?? 'Enter your email and password to sign in!' }}
                            </p>
                        </div>

                        @if (session('status'))
                            <div class="mb-4 text-sm font-medium text-[#0d5f59] bg-[#eef7f7] border border-[#d7ecec] rounded-lg px-4 py-2.5">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div>
                            <form action="{{ route('login') }}" method="POST">
                                @csrf

                                @if ($roleKey)
                                    <input type="hidden" name="role" value="{{ $roleKey }}">
                                @endif

                                <div class="space-y-5">
                                    <!-- Email -->
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Email<span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                            placeholder="you@******.gov.pg"
                                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-[#0f766e] focus:outline-none focus:ring-3 focus:ring-[#0f766e]/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-[#0f766e]" />
                                        @error('email')
                                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Password -->
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Password<span class="text-red-500">*</span>
                                        </label>
                                        <div x-data="{ showPassword: false }" class="relative">
                                            <input :type="showPassword ? 'text' : 'password'"
                                                id="password" name="password" required autocomplete="current-password"
                                                placeholder="Enter your password"
                                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-[#0f766e] focus:outline-none focus:ring-3 focus:ring-[#0f766e]/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-[#0f766e]" />
                                            <span @click="showPassword = !showPassword"
                                                class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 dark:text-gray-400">
                                                <svg x-show="!showPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z" fill="#98A2B3" />
                                                </svg>
                                                <svg x-show="showPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0064 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z" fill="#98A2B3" />
                                                </svg>
                                            </span>
                                        </div>
                                        @error('password')
                                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-[#0f766e] focus:ring-[#0f766e]">
                                            Remember me
                                        </label>

                                        @if (Route::has('password.request'))
                                            <a href="{{ route('password.request') }}" class="text-sm text-[#0f766e] hover:text-[#0d5f59] dark:text-[#2dd4bf]">
                                                Forgot password?
                                            </a>
                                        @endif
                                    </div>

                                    <!-- Button -->
                                    <div>
                                        <button type="submit"
                                            class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-[#0f766e] shadow-sm hover:bg-[#0d5f59]">
                                            Sign In
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
                                <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-[#0f766e] text-sm transition-colors">About MediTrack</a>
                                <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-[#0f766e] text-sm transition-colors">User Guide</a>
                                <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-[#0f766e] text-sm transition-colors">Support</a>
                                <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-[#0f766e] text-sm transition-colors">Data Security</a>
                                <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-[#0f766e] text-sm transition-colors">NDoH Portal</a>
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
</body>

</html>