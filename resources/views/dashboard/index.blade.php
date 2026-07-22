<x-app-layout>
    <x-dashboard.login-toasts />

    <div class="dashboard-shell">
        @if($roleMeta)
            @php
                $primaryAction = collect($quickActions ?? [])->firstWhere('primary', true);
                $quickActionCount = count($quickActions ?? []);
                $hour = now()->hour;
                $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                $firstName = explode(' ', trim(auth()->user()->name))[0];
                $topStats = collect($stats ?? [])->take(4);
            @endphp

            {{-- Page header --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="font-display text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $roleMeta['label'] }} Dashboard</h1>
                    <p class="mt-1 text-sm text-muted">{{ $greeting }}, {{ $firstName }} · {{ now()->format('l, j F Y') }}</p>
                </div>
                @if($primaryAction)
                    <a href="{{ $primaryAction['url'] }}" class="btn-brand inline-flex rounded-xl text-xs font-bold uppercase tracking-wide">
                        <x-dashboard.icon :name="$primaryAction['icon'] ?? 'plus'" class="mr-1.5 h-4 w-4" />
                        {{ $primaryAction['label'] }}
                    </a>
                @endif
            </div>

            {{-- Key metrics --}}
            @if($topStats->isNotEmpty())
                <section class="mb-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach($topStats as $stat)
                            <x-dashboard.stat-card
                                :label="$stat['label']"
                                :value="$stat['value']"
                                :hint="$stat['hint']"
                                :tone="$stat['tone']"
                                :url="$stat['url']"
                                :icon="$stat['icon'] ?? null"
                                :featured="$loop->first"
                            />
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Priority alerts --}}
            @if(count($alerts ?? []))
                <section class="mb-6 space-y-4">
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
                <section class="mb-6">
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

            {{-- Charts + activity --}}
            @if(count($charts ?? []) || count($recentItems ?? []))
                <section class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
                    @if(count($charts ?? []))
                        <div @class(['space-y-6', 'xl:col-span-2' => count($recentItems ?? [])])>
                            @foreach($charts as $chart)
                                <x-dashboard.chart :config="$chart" />
                            @endforeach
                        </div>
                    @endif

                    @if(count($recentItems ?? []))
                        <x-dashboard.recent-list :items="$recentItems" title="Recent activity" />
                    @endif
                </section>
            @endif

            {{-- Supply chain --}}
            @if($supplyChainHighlight ?? null)
                <section>
                    <x-dashboard.supply-chain :highlight="$supplyChainHighlight" />
                </section>
            @endif
        @else
            <div class="dashboard-empty-state">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50">
                    <x-dashboard.icon name="shield" class="h-8 w-8 text-brand-600" />
                </div>
                <h3 class="mt-6 heading-section">Welcome to MediTrack PNG</h3>
                <p class="mt-2 max-w-md text-sm text-muted">Your account has no portal role assigned. Contact an administrator for access.</p>
            </div>
        @endif
    </div>
</x-app-layout>
