<x-app-layout>
    <x-dashboard.login-toasts />

    <div class="dashboard-shell">
        @if($roleMeta)
            @php
                $greeting = portalGreeting();
                $firstName = explode(' ', trim(auth()->user()->name))[0];
                $topStats = collect($stats ?? [])->take(4);
            @endphp

            <div class="dashboard-welcome flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="heading-display">{{ $greeting }}, {{ $firstName }}</h1>
                    <p class="mt-1 text-sm text-ink-secondary dark:text-zinc-400">
                        {{ formatDate(now()) }}
                    </p>
                </div>
            </div>

            @if($topStats->isNotEmpty())
                <section class="mb-5">
                    <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
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
                <section class="mb-5">
                    <x-dashboard.pipeline-summary :counts="$pipelineCounts" />
                </section>
            @endif

            @if(count($alerts ?? []))
                <section class="mb-5 space-y-2">
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

            @php
                $insights = $insights ?? [];
                $overview = $supplyOverview ?? null;
                $hasInsights = ($insights['stockHealth'] ?? null) || ($insights['atRisk'] ?? null);
            @endphp
            @if($hasInsights)
                <section class="mb-5 space-y-3">
                    <h3 class="heading-section">Stock status</h3>
                    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
                        @if($insights['stockHealth'] ?? null)
                            <x-dashboard.stock-health-panel :panel="$insights['stockHealth']" />
                        @endif
                        @if($insights['atRisk'] ?? null)
                            <x-dashboard.at-risk-panel :panel="$insights['atRisk']" />
                        @endif
                    </div>
                </section>
            @endif

            @php
                $actionGroups = $quickActionGroups ?? [];
                if ($actionGroups === [] && count($quickActions ?? [])) {
                    $actionGroups = [['label' => 'Shortcuts', 'actions' => $quickActions]];
                }
            @endphp
            @if(count($actionGroups))
                <section class="mb-5 space-y-4">
                    @foreach($actionGroups as $group)
                        @php $groupActions = $group['actions'] ?? []; @endphp
                        @if(count($groupActions))
                            <div>
                                <p class="mb-2 text-section-label">{{ $group['label'] }}</p>
                                <div @class([
                                    'grid grid-cols-1 gap-2 sm:grid-cols-2',
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

            @if($overview)
                <section class="mb-5 space-y-3">
                    <h3 class="heading-section">Supply overview</h3>

                    <div class="grid grid-cols-1 gap-3 xl:grid-cols-3">
                        @if($overview['stockChart'] ?? null)
                            <div class="xl:col-span-2">
                                <x-dashboard.chart :config="$overview['stockChart']" />
                            </div>
                        @endif
                        @if($overview['statusDonut'] ?? null)
                            <x-dashboard.chart :config="$overview['statusDonut']" />
                        @endif
                    </div>

                    @if($overview['flow'] ?? null)
                        <x-dashboard.supply-flow :panel="$overview['flow']" />
                    @endif

                    <div @class([
                        'grid grid-cols-1 gap-3',
                        'xl:grid-cols-2' => ($overview['dispensing'] ?? null) || ($insights['shipments'] ?? null),
                    ])>
                        @if($overview['dispensing'] ?? null)
                            <x-dashboard.dispensing-chart
                                :config="$overview['dispensing']"
                                :url="$overview['dispensingUrl'] ?? null"
                                :drugs="$overview['dispensingDrugs'] ?? []"
                            />
                        @endif
                        @if($insights['shipments'] ?? null)
                            <x-dashboard.shipment-timeline :panel="$insights['shipments']" />
                        @endif
                    </div>

                    <div @class([
                        'grid grid-cols-1 gap-3',
                        'xl:grid-cols-2' => ($overview['activityChart'] ?? null) && ($insights['expiry'] ?? null),
                    ])>
                        @if($overview['activityChart'] ?? null)
                            <x-dashboard.chart :config="$overview['activityChart']" />
                        @endif
                        @if($insights['expiry'] ?? null)
                            <x-dashboard.expiry-timeline :panel="$insights['expiry']" />
                        @endif
                    </div>
                </section>
            @endif

            @if(count($charts ?? []) || count($recentItems ?? []))
                <section class="mb-5 grid grid-cols-1 gap-3 xl:grid-cols-3">
                    @if(count($charts ?? []))
                        <div @class(['space-y-3', 'xl:col-span-2' => count($recentItems ?? [])])>
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
                <h3 class="heading-section">No portal role assigned</h3>
                <p class="mt-2 max-w-md text-sm text-muted">Contact an administrator for MediTrack access.</p>
            </div>
        @endif
    </div>
</x-app-layout>
