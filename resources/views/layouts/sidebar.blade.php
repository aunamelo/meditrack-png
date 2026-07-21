@php
    $portalNav = $portalNav ?? \App\Services\PortalNavigationService::sections();
    $roleMeta = $roleMeta ?? \App\Services\PortalNavigationService::currentRoleMeta();
    $sectionLabels = config('portal.sections', []);
@endphp

<aside
    class="sidebar-shell fixed inset-y-0 left-0 z-40 w-64 transform transition-transform duration-200 ease-in-out lg:translate-x-0"
    :class="{ '-translate-x-full': ! sidebarOpen, 'translate-x-0': sidebarOpen }"
    x-cloak
>
    <div class="flex h-full flex-col">
        <div class="flex h-16 items-center gap-3 border-b border-line px-4 dark:border-zinc-800">
            <a href="{{ getRoleDashboardRoute() }}" class="flex min-w-0 items-center gap-2.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-extrabold text-white shadow-glow">M</div>
                <div class="min-w-0">
                    <p class="truncate font-display text-sm font-bold text-ink dark:text-zinc-50">MediTrack PNG</p>
                    <p class="truncate text-[11px] font-medium text-ink-muted dark:text-zinc-500">Medicine supply chain</p>
                </div>
            </a>
        </div>

        @if($roleMeta)
            <div class="border-b border-line px-4 py-3.5 dark:border-zinc-800">
                <p class="text-section-label">{{ $roleMeta['label'] }}</p>
                <p class="mt-1 text-xs font-medium text-ink-muted dark:text-zinc-500">{{ $roleMeta['subtitle'] }}</p>
            </div>
        @endif

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
            @foreach($portalNav as $sectionKey => $items)
                <div>
                    <p class="mb-2 px-2 text-section-label">
                        {{ $sectionLabels[$sectionKey] ?? ucfirst($sectionKey) }}
                    </p>
                    <div class="space-y-1">
                        @foreach($items as $item)
                            <x-sidebar-link
                                :href="$item['href']"
                                :active="$item['active']"
                                :icon="$item['icon']"
                                :label="$item['label']"
                                :description="$item['description']"
                                :badge="$item['badge'] ?? null"
                            />
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-line p-3 dark:border-zinc-800">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-ink-muted transition hover:bg-surface-muted hover:text-ink dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-100">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-950 dark:text-brand-300">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-semibold">{{ Auth::user()->name }}</span>
                    <span class="block truncate text-xs text-ink-faint dark:text-zinc-500">Account settings</span>
                </span>
            </a>
        </div>
    </div>
</aside>

<div
    x-show="sidebarOpen"
    x-transition.opacity
    @click="sidebarOpen = false"
    class="fixed inset-0 z-30 bg-ink/60 backdrop-blur-sm lg:hidden dark:bg-black/70"
    x-cloak
></div>
