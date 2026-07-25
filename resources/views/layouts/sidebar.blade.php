@php
    $portalNav = $portalNav ?? \App\Services\PortalNavigationService::sections();
    $roleMeta = $roleMeta ?? \App\Services\PortalNavigationService::currentRoleMeta();
    $navGroups = config('portal.nav_groups', ['menu' => [], 'other' => []]);
@endphp

<aside
    class="medcare-sidebar fixed inset-y-0 left-0 z-40 h-full min-h-screen transform transition-transform duration-200 ease-in-out lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:translate-x-0"
    :class="{
        '-translate-x-full': ! sidebarOpen,
        'translate-x-0': sidebarOpen,
        'medcare-sidebar-collapsed': sidebarCollapsed,
    }"
    x-cloak
>
    <div class="flex h-full min-h-screen flex-col overflow-hidden">
        {{-- Brand + collapse toggle --}}
        <div class="sidebar-header flex items-center gap-2 px-4 py-5 lg:px-3">
            <a href="{{ getRoleDashboardRoute() }}" class="sidebar-header-brand flex min-w-0 flex-1 items-center gap-3 overflow-hidden" title="MediTrack PNG">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white p-1">
                    <x-app-icon size="sm" class="h-9 w-9 object-contain" />
                </div>
                <div class="sidebar-brand-text min-w-0">
                    <p class="truncate font-display text-lg font-bold text-white">MediTrack</p>
                    <p class="truncate text-xs font-medium text-brand-100">NDoH eLog Portal</p>
                </div>
            </a>
            <button
                type="button"
                @click="toggleSidebarCollapse()"
                class="sidebar-collapse-btn sidebar-header-collapse"
                :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                aria-expanded="true"
                x-bind:aria-expanded="(! sidebarCollapsed).toString()"
            >
                <svg
                    class="h-4 w-4 transition-transform duration-300"
                    :class="{ 'rotate-180': sidebarCollapsed }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto overflow-x-hidden px-3 pb-4">
            @foreach($navGroups as $groupLabel => $sectionKeys)
                @php
                    $groupItems = collect($sectionKeys)
                        ->filter(fn ($key) => isset($portalNav[$key]))
                        ->flatMap(fn ($key) => $portalNav[$key])
                        ->values();
                @endphp

                @if($groupItems->isNotEmpty())
                    <div>
                        <p class="medcare-sidebar-label mb-2">
                            {{ $groupLabel === 'menu' ? 'Menu' : 'Other Menu' }}
                        </p>
                        <div class="space-y-1">
                            @foreach($groupItems as $item)
                                <x-sidebar-link
                                    :href="$item['href']"
                                    :active="$item['active']"
                                    :icon="$item['icon']"
                                    :label="$item['label']"
                                    :badge="$item['badge'] ?? null"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        @if($roleMeta)
            <div class="sidebar-role-card mx-3 mb-3 overflow-hidden rounded-2xl bg-white/10 px-4 py-3 backdrop-blur-sm" title="{{ $roleMeta['label'] }} · {{ $roleMeta['inventory_label'] }}">
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/50">Your role</p>
                <p class="mt-1 truncate text-sm font-semibold text-white">{{ $roleMeta['label'] }}</p>
                <p class="mt-0.5 truncate text-xs text-white/60">{{ $roleMeta['inventory_label'] }}</p>
            </div>
        @endif

        {{-- Expand control when collapsed --}}
        <div class="sidebar-collapse-footer">
            <button
                type="button"
                @click="toggleSidebarCollapse()"
                class="sidebar-collapse-btn"
                title="Expand sidebar"
                aria-label="Expand sidebar"
            >
                <svg
                    class="h-4 w-4 rotate-180"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>
    </div>
</aside>

<div
    x-show="sidebarOpen"
    x-transition.opacity
    @click="sidebarOpen = false"
    class="fixed inset-0 z-30 bg-ink/50 backdrop-blur-sm lg:hidden"
    x-cloak
></div>
