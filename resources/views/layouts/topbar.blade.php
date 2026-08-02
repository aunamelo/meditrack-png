@php
    $roleMeta = $roleMeta ?? \App\Services\PortalNavigationService::currentRoleMeta();
    $primaryAction = \App\Services\PortalNavigationService::primaryAction();
    $inboxNotifications = \App\Services\NotificationInboxService::recentFor(auth()->user(), 10);
    $unreadCount = \App\Services\NotificationInboxService::unreadCount(auth()->user());
@endphp

<header class="medcare-topbar z-30 shrink-0" role="banner">
    <div class="flex min-w-0 flex-1 items-center gap-3">
        <button
            type="button"
            @click="sidebarOpen = ! sidebarOpen"
            class="inline-flex items-center justify-center rounded-lg p-2.5 text-slate-500 transition hover:bg-health-50 hover:text-health-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-health-600 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-health-300 lg:hidden"
            aria-label="Open navigation menu"
            aria-controls="app-sidebar"
            :aria-expanded="sidebarOpen.toString()"
        >
            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        @isset($header)
            <div class="min-w-0 flex-1">{{ $header }}</div>
        @else
            <div class="min-w-0 flex-1">
                <p class="m-0 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 dark:text-zinc-400">
                    {{ portalWorkspaceScope($roleMeta) }}
                </p>
                <p class="m-0 truncate font-sans text-sm font-semibold text-ink dark:text-zinc-50">
                    {{ $roleMeta['label'] ?? 'Workspace' }}
                </p>
            </div>
        @endisset
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        @if($primaryAction)
            <a href="{{ $primaryAction['url'] }}" class="btn-brand-outline hidden px-4 py-2 text-xs font-bold uppercase tracking-wide sm:inline-flex focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-health-600">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                {{ $primaryAction['label'] }}
            </a>
        @endif

        <div
            class="relative"
            x-data="{ open: false }"
            @click.outside="open = false"
            @keydown.escape.window="open = false"
        >
            <button
                type="button"
                @click="open = ! open"
                class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-500 transition hover:text-health-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-health-600 dark:border-white/10 dark:bg-night-elevated"
                aria-label="{{ $unreadCount > 0 ? 'Notifications, '.$unreadCount.' unread' : 'Notifications' }}"
                aria-haspopup="true"
                :aria-expanded="open.toString()"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($unreadCount > 0)
                    <span class="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#ce1126] px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white" aria-hidden="true">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                    <span class="sr-only">{{ $unreadCount }} unread</span>
                @endif
            </button>

            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                class="absolute end-0 z-50 mt-2 w-[min(100vw-2rem,22rem)] overflow-hidden rounded-xl border border-line bg-surface shadow-soft dark:border-zinc-700 dark:bg-zinc-900"
                role="region"
                aria-label="Notifications"
            >
                <div class="flex items-center justify-between border-b border-line px-4 py-3 dark:border-zinc-800">
                    <p class="text-sm font-semibold text-ink dark:text-zinc-100">Notifications</p>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400">
                                Mark all read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-80 divide-y divide-line overflow-y-auto dark:divide-zinc-800">
                    @forelse($inboxNotifications as $notification)
                        @php
                            $url = \App\Services\NotificationInboxService::actionUrl($notification);
                            $message = $notification->data['message'] ?? 'Service update';
                            $reference = $notification->data['reference']
                                ?? $notification->data['order_number']
                                ?? $notification->data['transfer_number']
                                ?? null;
                            $isUnread = is_null($notification->read_at);
                        @endphp
                        @if($url)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex w-full flex-col gap-0.5 px-4 py-3 text-left transition hover:bg-surface-muted dark:hover:bg-zinc-800 {{ $isUnread ? 'bg-brand-50/50 dark:bg-brand-950/20' : '' }}"
                                >
                                    <span class="text-sm font-medium text-ink dark:text-zinc-100">{{ $message }}</span>
                                    <span class="text-xs text-muted">
                                        @if($reference){{ $reference }} · @endif{{ $notification->created_at?->diffForHumans() }}
                                        @if($isUnread) · Unread @endif
                                    </span>
                                </button>
                            </form>
                        @else
                            <div class="px-4 py-3 {{ $isUnread ? 'bg-brand-50/50 dark:bg-brand-950/20' : '' }}">
                                <p class="text-sm font-medium text-ink dark:text-zinc-100">{{ $message }}</p>
                                <p class="text-xs text-muted">{{ $notification->created_at?->diffForHumans() }}</p>
                            </div>
                        @endif
                    @empty
                        <p class="px-4 py-6 text-sm text-muted">No notifications yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <x-theme-toggle compact />

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button
                    type="button"
                    class="inline-flex items-center gap-2.5 rounded-lg border border-slate-300 bg-white py-1.5 pl-1.5 pr-3 text-sm font-semibold text-[#132f4f] transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 dark:border-white/10 dark:bg-night-elevated dark:text-zinc-100"
                    aria-haspopup="menu"
                    aria-label="Account menu for {{ Auth::user()->name }}"
                >
                    <x-user-avatar size="sm" class="!rounded-lg" />
                    <span class="hidden max-w-[120px] truncate sm:inline">{{ Auth::user()->name }}</span>
                    <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                @if($roleMeta)
                    <div class="border-b border-slate-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-xs font-semibold text-[#132f4f] dark:text-zinc-100">{{ $roleMeta['label'] }}</p>
                        <p class="text-[11px] text-muted">{{ $roleMeta['inventory_label'] }}</p>
                    </div>
                @endif

                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-dropdown-link>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    data-confirm="Are you sure you want to log out?"
                    data-confirm-title="Log out"
                    data-confirm-label="Log out"
                >
                    @csrf
                    <button type="submit" class="block w-full px-4 py-2.5 text-start text-sm font-medium leading-5 text-ink-secondary transition hover:bg-slate-50 focus:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-brand-600 dark:hover:bg-zinc-800">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
