@php
    $roleMeta = $roleMeta ?? \App\Services\PortalNavigationService::currentRoleMeta();
    $primaryAction = \App\Services\PortalNavigationService::primaryAction();
    $pendingBadge = ($roleMeta && auth()->user()->hasRole('admin'))
        ? \App\Models\Order::pending()->count()
        : 0;
@endphp

<header class="medcare-topbar z-30 shrink-0">
    <div class="flex min-w-0 flex-1 items-center gap-3">
        <button
            type="button"
            @click="sidebarOpen = ! sidebarOpen"
            class="inline-flex items-center justify-center rounded-xl p-2.5 text-ink-muted transition hover:bg-canvas hover:text-ink lg:hidden"
        >
            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        @isset($header)
            <div class="min-w-0 flex-1">{{ $header }}</div>
        @endisset
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        @if($primaryAction)
            <a href="{{ $primaryAction['url'] }}" class="btn-brand hidden rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-wide sm:inline-flex">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                {{ $primaryAction['label'] }}
            </a>
        @endif

        <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-canvas text-ink-muted transition hover:text-brand-600" aria-label="Notifications">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            @if($pendingBadge > 0)
                <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-accent ring-2 ring-surface"></span>
            @endif
        </button>

        <x-theme-toggle compact />

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center gap-2.5 rounded-xl border border-line bg-surface py-1.5 pl-1.5 pr-3 text-sm font-semibold text-ink transition hover:bg-canvas">
                    <x-user-avatar size="sm" class="!rounded-lg" />
                    <span class="hidden max-w-[120px] truncate sm:inline">{{ Auth::user()->name }}</span>
                    <svg class="h-4 w-4 text-ink-faint" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                @if($roleMeta)
                    <div class="border-b border-line px-4 py-3 dark:border-zinc-800">
                        <p class="text-xs font-semibold text-ink">{{ $roleMeta['label'] }}</p>
                        <p class="text-[11px] text-muted">{{ $roleMeta['inventory_label'] }}</p>
                    </div>
                @endif

                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Are you sure you want to log out?')">
                    @csrf
                    <button type="submit" class="block w-full px-4 py-2.5 text-start text-sm font-medium leading-5 text-ink-secondary transition hover:bg-canvas focus:bg-canvas focus:outline-none">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
