<x-app-layout>
    <x-slot name="header">
        <h2 class="heading-page">Dashboard</h2>
    </x-slot>

    <x-dashboard.login-toasts />

    <div class="dashboard-shell">
        @if($roleMeta)
            @php
                $primaryAction = collect($quickActions ?? [])->firstWhere('primary', true);
                $stats = array_slice($stats ?? [], 0, 3);
                $charts = $charts ?? [];
                $recentItems = array_slice($recentItems ?? [], 0, 4);
            @endphp

            {{-- Welcome --}}
            <div class="dashboard-welcome">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm text-muted">{{ now()->format('l, j F Y') }}</p>
                        <h1 class="mt-1 font-display text-2xl font-semibold tracking-tight text-ink dark:text-zinc-50">
                            Hello, {{ auth()->user()->name }}
                        </h1>
                        <p class="mt-1 text-sm text-muted">
                            {{ $roleMeta['label'] }} · {{ $roleMeta['inventory_label'] }}
                        </p>
                    </div>
                    @if($primaryAction)
                        <a href="{{ $primaryAction['url'] }}" class="btn-brand shrink-0">
                            {{ $primaryAction['label'] }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Priority alerts --}}
            @foreach($alerts as $alert)
                <x-dashboard.alert-panel
                    class="mb-6"
                    :tone="$alert['tone']"
                    :title="$alert['title']"
                    :message="$alert['message']"
                    :action-label="$alert['action_label'] ?? null"
                    :action-url="$alert['action_url'] ?? null"
                    :badge="$alert['badge'] ?? null"
                    :items="$alert['items'] ?? []"
                />
            @endforeach

            {{-- Key metrics --}}
            @if(count($stats))
                <div class="mb-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
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

            {{-- Insights + activity --}}
            @if(count($charts) || count($recentItems))
                <div @class([
                    'grid grid-cols-1 gap-6',
                    'lg:grid-cols-3' => count($charts) && count($recentItems),
                ])>
                    @if(count($charts))
                        <div @class(['space-y-6', 'lg:col-span-2' => count($recentItems)])>
                            @foreach($charts as $chart)
                                <x-dashboard.chart :config="$chart" />
                            @endforeach
                        </div>
                    @endif

                    @if(count($recentItems))
                        <x-dashboard.recent-list :items="$recentItems" />
                    @endif
                </div>
            @endif
        @else
            <div class="surface-panel p-8 text-center">
                <h3 class="heading-section">Welcome to MediTrack PNG</h3>
                <p class="mt-2 text-sm text-muted">Your account has no portal role assigned. Contact an administrator for access.</p>
            </div>
        @endif
    </div>
</x-app-layout>
