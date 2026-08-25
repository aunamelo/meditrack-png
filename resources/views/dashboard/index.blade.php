<x-app-layout>
    <x-dashboard.login-toasts />

    <div class="dashboard-shell">
        @if($roleMeta)
            @php
                $greeting = portalGreeting();
                $firstName = explode(' ', trim(auth()->user()->name))[0];
                $topStats = collect($stats ?? [])->take(4);
                $showWelcomeBanner = ($roleMeta['key'] ?? null) === 'store_manager'
                    && $topStats->isNotEmpty()
                    && $topStats->every(function ($stat) {
                        $raw = str_replace(',', '', (string) ($stat['value'] ?? '0'));

                        return is_numeric($raw) && (float) $raw === 0.0;
                    });
            @endphp

            <div
                @if($showWelcomeBanner)
                    x-data="{ welcomeOpen: localStorage.getItem('mt-sm-welcome-dismissed') !== '1' }"
                @endif
            >
                <div class="dashboard-welcome flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="heading-display">{{ $greeting }}, {{ $firstName }}</h1>
                        <p class="mt-1 text-sm text-ink-secondary dark:text-zinc-400">
                            {{ now()->timezone(config('app.timezone'))->format('l, j M Y') }}
                        </p>
                    </div>
                </div>

                @if($showWelcomeBanner)
                    <div
                        x-show="welcomeOpen"
                        x-cloak
                        class="mb-6 mt-4 flex items-start gap-3 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-800 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-100"
                        role="status"
                    >
                        <x-dashboard.icon name="info" class="mt-0.5 h-5 w-5 shrink-0" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">Welcome to MediTrack PNG!</p>
                            <p class="mt-1 text-xs text-teal-700 dark:text-teal-200/90">
                                Your dashboard will populate automatically as you receive inventory, process hospital orders, and track shipments.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="rounded-md p-1 text-teal-700 transition hover:bg-teal-100 dark:text-teal-200 dark:hover:bg-teal-900/50"
                            aria-label="Dismiss welcome message"
                            @click="welcomeOpen = false; localStorage.setItem('mt-sm-welcome-dismissed', '1')"
                        >
                            <x-dashboard.icon name="x" class="h-4 w-4" />
                        </button>
                    </div>
                @endif
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
                $flatActions = collect($actionGroups)->flatMap(fn ($group) => $group['actions'] ?? [])->values();
            @endphp
            @if($flatActions->isNotEmpty())
                <section class="mb-5">
                    <div @class([
                        'grid grid-cols-1 gap-2 sm:grid-cols-2',
                        'xl:grid-cols-2' => $flatActions->count() <= 2,
                        'xl:grid-cols-3' => $flatActions->count() >= 3,
                    ])>
                        @foreach($flatActions as $action)
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
