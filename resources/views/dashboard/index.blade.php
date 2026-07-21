<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Overview</p>
            <h2 class="heading-page">{{ $roleMeta['label'] ?? 'Dashboard' }}</h2>
            <p class="mt-0.5 text-sm font-medium text-muted">{{ $roleMeta['subtitle'] ?? 'MediTrack PNG portal' }}</p>
        </div>
    </x-slot>

    <x-dashboard.login-toasts />

    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-8">
            @if($roleMeta)
                <div class="hero-banner">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-brand-600 dark:text-brand-400">Welcome back, {{ auth()->user()->name }}</p>
                            <h1 class="heading-display mt-1 text-balance">
                                {{ $roleMeta['label'] }} workspace
                            </h1>
                            <p class="mt-3 max-w-2xl text-sm font-medium text-muted">
                                Manage {{ strtolower($roleMeta['inventory_label']) }} inventory, procurement, and logistics from one place.
                            </p>
                        </div>
                        <div class="text-sm font-semibold text-ink-faint dark:text-zinc-500">
                            {{ now()->format('l, j F Y') }}
                        </div>
                    </div>
                </div>

                @if(count($stats))
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach($stats as $stat)
                            <x-dashboard.stat-card
                                :label="$stat['label']"
                                :value="$stat['value']"
                                :hint="$stat['hint']"
                                :tone="$stat['tone']"
                                :url="$stat['url']"
                            />
                        @endforeach
                    </div>
                @endif

                @if(count($charts ?? []))
                    <div>
                        <h3 class="text-section-label mb-4">Analytics</h3>
                        <div @class([
                            'grid grid-cols-1 gap-6',
                            'lg:grid-cols-2' => count($charts) > 1,
                        ])>
                            @foreach($charts as $chart)
                                <x-dashboard.chart :config="$chart" @class(['lg:col-span-2' => count($charts) === 1]) />
                            @endforeach
                        </div>
                    </div>
                @endif

                <x-dashboard.supply-chain :highlight="$supplyChainHighlight" />

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

                @if(count($quickActions))
                    <div>
                        <h3 class="text-section-label mb-4">Modules</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach($quickActions as $action)
                                <x-dashboard.module-card
                                    :label="$action['label']"
                                    :description="$action['description']"
                                    :url="$action['url']"
                                    :primary="$action['primary'] ?? false"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($recentItems))
                    <div class="surface-panel">
                        <div class="border-b border-line px-6 py-4 dark:border-zinc-800">
                            <h3 class="heading-section">Recent activity</h3>
                        </div>
                        <ul class="divide-y divide-line dark:divide-zinc-800">
                            @foreach($recentItems as $item)
                                <li>
                                    <a href="{{ $item['url'] }}" class="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-surface-muted dark:hover:bg-zinc-800/50">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-ink dark:text-zinc-100">{{ $item['title'] }}</p>
                                            <p class="truncate text-sm text-muted">{{ $item['subtitle'] }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-surface-muted px-2.5 py-1 text-xs font-semibold text-ink-secondary dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $item['meta'] }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @else
                <div class="surface-panel p-8">
                    <h3 class="heading-section">Welcome to MediTrack PNG</h3>
                    <p class="mt-2 text-sm text-muted">Your account is active but no portal role is assigned. Contact an administrator to get access.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
