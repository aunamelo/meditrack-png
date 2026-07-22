<x-app-layout>
    <x-slot name="header">
        <div>
            @if($roleMeta ?? null)
                <p class="text-section-label">{{ $roleMeta['label'] }}</p>
                <h2 class="heading-page">{{ $roleMeta['label'] }} Dashboard</h2>
            @else
                <p class="text-section-label">Portal</p>
                <h2 class="heading-page">Dashboard</h2>
            @endif
        </div>
    </x-slot>

    <x-dashboard.login-toasts />

    <div class="dashboard-shell">
        @if($roleMeta)
            @php
                $primaryAction = collect($quickActions ?? [])->firstWhere('primary', true);
                $quickActionCount = count($quickActions ?? []);
                $hour = now()->hour;
                $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                $firstName = explode(' ', trim(auth()->user()->name))[0];
            @endphp

            {{-- Hero --}}
            <section class="dashboard-hero">
                <div class="dashboard-hero-pattern" aria-hidden="true"></div>
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-medium text-brand-100/90">{{ now()->format('l, j F Y') }}</p>
                        <h1 class="mt-2 font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">
                            {{ $greeting }}, {{ $firstName }}
                        </h1>
                        <p class="mt-3 text-base text-brand-50/90">
                            {{ $roleMeta['subtitle'] ?? 'MediTrack PNG supply chain portal' }}
                        </p>
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                                {{ $roleMeta['label'] }}
                            </span>
                            <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-brand-50/90 backdrop-blur-sm">
                                {{ $roleMeta['inventory_label'] }}
                            </span>
                        </div>
                    </div>
                    @if($primaryAction)
                        <a href="{{ $primaryAction['url'] }}" class="dashboard-hero-cta group">
                            <x-dashboard.icon :name="$primaryAction['icon'] ?? 'plus'" class="h-5 w-5" />
                            <span>{{ $primaryAction['label'] }}</span>
                            <svg class="ml-1 h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </section>

            {{-- Supply chain --}}
            @if($supplyChainHighlight ?? null)
                <section class="mb-8">
                    <x-dashboard.supply-chain :highlight="$supplyChainHighlight" />
                </section>
            @endif

            {{-- Key metrics --}}
            @if(count($stats ?? []))
                <section class="mb-8">
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-section-label">At a glance</p>
                            <h3 class="heading-section">Key metrics</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($stats as $stat)
                            <x-dashboard.stat-card
                                :label="$stat['label']"
                                :value="$stat['value']"
                                :hint="$stat['hint']"
                                :tone="$stat['tone']"
                                :url="$stat['url']"
                                :icon="$stat['icon'] ?? null"
                            />
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Priority alerts --}}
            @if(count($alerts ?? []))
                <section class="mb-8 space-y-4">
                    @foreach($alerts as $alert)
                        <x-dashboard.alert-panel
                            :tone="$alert['tone']"
                            :title="$alert['title']"
                            :message="$alert['message']"
                            :action-label="$alert['action_label'] ?? null"
                            :action-url="$alert['action_url'] ?? null"
                            :badge="$alert['badge'] ?? null"
                            :items="$alert['items'] ?? []"
                        />
                    @endforeach
                </section>
            @endif

            {{-- Quick access --}}
            @if(count($quickActions ?? []))
                <section class="mb-8">
                    <div class="mb-4">
                        <p class="text-section-label">Shortcuts</p>
                        <h3 class="heading-section">Quick access</h3>
                    </div>
                    <div @class([
                        'grid grid-cols-1 gap-4 sm:grid-cols-2',
                        'xl:grid-cols-2' => $quickActionCount === 2,
                        'xl:grid-cols-3' => $quickActionCount === 3,
                        'xl:grid-cols-4' => $quickActionCount >= 4,
                    ])>
                        @foreach($quickActions as $action)
                            <x-dashboard.module-card
                                :label="$action['label']"
                                :description="$action['description']"
                                :url="$action['url']"
                                :primary="$action['primary'] ?? false"
                                :icon="$action['icon'] ?? 'cube'"
                            />
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Insights + activity --}}
            @if(count($charts ?? []) || count($recentItems ?? []))
                <section>
                    <div class="mb-4">
                        <p class="text-section-label">Insights</p>
                        <h3 class="heading-section">Charts & recent activity</h3>
                    </div>
                    <div @class([
                        'grid grid-cols-1 gap-6',
                        'xl:grid-cols-3' => count($charts ?? []) && count($recentItems ?? []),
                    ])>
                        @if(count($charts ?? []))
                            <div @class(['space-y-6', 'xl:col-span-2' => count($recentItems ?? [])])>
                                @foreach($charts as $chart)
                                    <x-dashboard.chart :config="$chart" />
                                @endforeach
                            </div>
                        @endif

                        @if(count($recentItems ?? []))
                            <x-dashboard.recent-list :items="$recentItems" />
                        @endif
                    </div>
                </section>
            @endif
        @else
            <div class="dashboard-empty-state">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-100 dark:bg-brand-950">
                    <x-dashboard.icon name="shield" class="h-8 w-8 text-brand-600 dark:text-brand-400" />
                </div>
                <h3 class="mt-6 heading-section">Welcome to MediTrack PNG</h3>
                <p class="mt-2 max-w-md text-sm text-muted">Your account has no portal role assigned. Contact an administrator for access.</p>
            </div>
        @endif
    </div>
</x-app-layout>
