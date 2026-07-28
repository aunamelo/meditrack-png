<nav x-data="{ open: false }" class="topbar-shell border-b shadow-sm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <div class="flex shrink-0 items-center">
                    <a href="{{ getRoleDashboardRoute() }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="getRoleDashboardRoute()" :active="request()->routeIs('dashboard', 'dashboard.admin', 'dashboard.procurement_officer', 'dashboard.store_manager', 'dashboard.pharmacy_manager', 'dashboard.pharmacist')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if(auth()->user()->hasAnyRole(['admin', 'procurement_officer', 'store_manager', 'pharmacy_manager', 'pharmacist']))
                        <x-nav-link :href="getDashboardDrugRoute('index')" :active="request()->routeIs('*.dashboard.drugs.*')">
                            {{ __('Drug Inventory') }}
                        </x-nav-link>
                        <x-nav-link :href="getDashboardOrderRoute('index')" :active="request()->routeIs('*.dashboard.orders.*')">
                            {{ __('Procurement Orders') }}
                        </x-nav-link>
                    @endif
                    @if(auth()->user()->hasAnyRole(['admin', 'procurement_officer', 'store_manager']))
                        <x-nav-link :href="getDashboardTransferRoute('index')" :active="request()->routeIs('*.dashboard.transfers.*')">
                            {{ __('Shipments to Lae AMS') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Are you sure you want to log out?')">
                            @csrf
                            <button type="submit" class="block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-ink-muted transition hover:border-line hover:bg-surface-muted hover:text-ink focus:border-line focus:bg-surface-muted focus:text-ink focus:outline-none dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:bg-zinc-900 dark:hover:text-zinc-200">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="getRoleDashboardRoute()" :active="request()->routeIs('dashboard', 'dashboard.admin', 'dashboard.procurement_officer', 'dashboard.store_manager', 'dashboard.pharmacy_manager', 'dashboard.pharmacist')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if(auth()->user()->hasAnyRole(['admin', 'procurement_officer', 'store_manager', 'pharmacy_manager', 'pharmacist']))
                <x-responsive-nav-link :href="getDashboardDrugRoute('index')" :active="request()->routeIs('*.dashboard.drugs.*')">
                    {{ __('Drug Inventory') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="getDashboardOrderRoute('index')" :active="request()->routeIs('*.dashboard.orders.*')">
                    {{ __('Procurement Orders') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->hasAnyRole(['admin', 'procurement_officer', 'store_manager']))
                <x-responsive-nav-link :href="getDashboardTransferRoute('index')" :active="request()->routeIs('*.dashboard.transfers.*')">
                    {{ __('Shipments to Lae AMS') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Are you sure you want to log out?')">
                    @csrf
                    <button type="submit" class="block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-ink-muted transition hover:border-line hover:bg-surface-muted hover:text-ink focus:border-line focus:bg-surface-muted focus:text-ink focus:outline-none dark:text-zinc-400 dark:hover:border-zinc-700 dark:hover:bg-zinc-900 dark:hover:text-zinc-200">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
