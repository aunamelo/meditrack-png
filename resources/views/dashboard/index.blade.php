<x-app-layout>
    <x-dashboard.login-toasts />

    <div class="dashboard-shell">
        @if($roleMeta)
            @php
                $greeting = portalGreeting();
                $firstName = explode(' ', trim(auth()->user()->name))[0];
                $topStats = collect($stats ?? [])->take(4);
            @endphp

            {{-- Page header --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div @class(['flex gap-4', 'items-center' => in_array($roleMeta['key'] ?? '', ['pharmacy_manager', 'pharmacist'], true)])>
                    @if(in_array($roleMeta['key'] ?? '', ['pharmacy_manager', 'pharmacist'], true))
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white p-1.5 shadow-soft ring-1 ring-line dark:bg-zinc-900 dark:ring-zinc-800">
                            <x-app-icon size="lg" class="h-full w-full object-contain" />
                        </div>
                    @endif
                    <div>
                        <h1 class="font-display text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $roleMeta['label'] }} Dashboard</h1>
                        <p class="mt-1 text-sm text-muted">
                            {{ $greeting }}, {{ $firstName }} · {{ now()->format('l, j F Y') }}
                            @if(in_array($roleMeta['key'] ?? '', ['pharmacy_manager', 'pharmacist'], true))
                                · {{ $roleMeta['brand_tagline'] ?? 'Modilon General Hospital' }}
                            @endif
                        </p>
                    </div>
                </div>
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

            @if(isset($pipelineCounts) && in_array($roleMeta['key'] ?? '', ['admin', 'procurement_officer'], true))
                <section class="mb-6">
                    <x-dashboard.pipeline-summary :counts="$pipelineCounts" />
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

            {{-- Interactive insights --}}
            @php
                $insights = $insights ?? [];
                $hasInsights = ($insights['stockHealth'] ?? null)
                    || ($insights['atRisk'] ?? null)
                    || ($insights['expiry'] ?? null)
                    || ($insights['dispenseTrend'] ?? null);
            @endphp
            @if($hasInsights)
                <section class="mb-6 space-y-6">
                    <div>
                        <p class="text-section-label">Insights</p>
                        <h3 class="heading-section">Stock intelligence</h3>
                    </div>

                    @if(($insights['stockHealth'] ?? null) || ($insights['atRisk'] ?? null))
                        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                            @if($insights['stockHealth'] ?? null)
                                <x-dashboard.stock-health-panel :panel="$insights['stockHealth']" />
                            @endif
                            @if($insights['atRisk'] ?? null)
                                <x-dashboard.at-risk-panel :panel="$insights['atRisk']" />
                            @endif
                        </div>
                    @endif

                    @if(($insights['expiry'] ?? null) || ($insights['dispenseTrend'] ?? null))
                        <div @class([
                            'grid grid-cols-1 gap-6',
                            'xl:grid-cols-2' => ($insights['expiry'] ?? null) && ($insights['dispenseTrend'] ?? null),
                        ])>
                            @if($insights['expiry'] ?? null)
                                <x-dashboard.expiry-timeline :panel="$insights['expiry']" />
                            @endif
                            @if($insights['dispenseTrend'] ?? null)
                                <x-dashboard.dispense-trend :panel="$insights['dispenseTrend']" />
                            @endif
                        </div>
                    @endif
                </section>
            @endif

            {{-- Quick access (workflow groups matching sidebar) --}}
            @php
                $actionGroups = $quickActionGroups ?? [];
                if ($actionGroups === [] && count($quickActions ?? [])) {
                    $actionGroups = [['label' => 'Shortcuts', 'actions' => $quickActions]];
                }
            @endphp
            @if(count($actionGroups))
                <section class="mb-6 space-y-6">
                    <div class="mb-1">
                        <p class="text-section-label">Shortcuts</p>
                        <h3 class="heading-section">Quick access</h3>
                    </div>
                    @foreach($actionGroups as $group)
                        @php $groupActions = $group['actions'] ?? []; @endphp
                        @if(count($groupActions))
                            <div>
                                <p class="mb-3 text-xs font-bold uppercase tracking-widest text-muted">{{ $group['label'] }}</p>
                                <div @class([
                                    'grid grid-cols-1 gap-4 sm:grid-cols-2',
                                    'xl:grid-cols-2' => count($groupActions) <= 2,
                                    'xl:grid-cols-3' => count($groupActions) >= 3,
                                ])>
                                    @foreach($groupActions as $action)
                                        <x-dashboard.module-card
                                            :label="$action['label']"
                                            :description="$action['description']"
                                            :url="$action['url']"
                                            :primary="$action['primary'] ?? false"
                                            :icon="$action['icon'] ?? 'cube'"
                                        />
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
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
                        <x-dashboard.recent-list :items="$recentItems" :title="$recentTitle ?? 'Recent activity'" />
                    @endif
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
