<header class="topbar-shell sticky top-0 z-20 flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-3">
        <button
            type="button"
            @click="sidebarOpen = ! sidebarOpen"
            class="inline-flex items-center justify-center rounded-lg p-2 text-ink-muted transition hover:bg-surface-muted hover:text-ink dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 lg:hidden"
        >
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        @isset($header)
            <div class="min-w-0">{{ $header }}</div>
        @else
            <h1 class="heading-page truncate">MediTrack PNG</h1>
        @endisset
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        @if($roleMeta ?? \App\Services\PortalNavigationService::currentRoleMeta())
            <span class="badge-brand hidden sm:inline-flex">
                {{ ($roleMeta ?? \App\Services\PortalNavigationService::currentRoleMeta())['inventory_label'] ?? 'Portal' }}
            </span>
        @endif

        <x-theme-toggle compact />

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center gap-2 rounded-lg border border-line px-3 py-2 text-sm font-semibold text-ink-secondary transition hover:bg-surface-muted dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                    <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                    <svg class="h-4 w-4 text-ink-faint dark:text-zinc-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Are you sure you want to log out?')">
                    @csrf
                    <button type="submit" class="block w-full px-4 py-2.5 text-start text-sm font-medium leading-5 text-ink-secondary transition hover:bg-surface-muted focus:bg-surface-muted focus:outline-none dark:text-zinc-300 dark:hover:bg-zinc-800 dark:focus:bg-zinc-800">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
