<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Administration</p>
            <h2 class="heading-page">Audit Logs</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <div class="module-actions-bar">
            <x-module.back-link :href="route('dashboard.admin')" label="Back to Dashboard" />
        </div>

        <div class="module-form-shell">
            <form action="{{ route('admin.dashboard.audit-logs.index') }}" method="GET" class="mb-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                        <select name="user_id" id="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                            <option value="">All Users</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="action" class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                        <input type="text" name="action" id="action" value="{{ request('action') }}" placeholder="e.g., login, create, update" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Filter</button>
                    <a href="{{ route('admin.dashboard.audit-logs.index') }}" class="btn-module-secondary">Clear Filters</a>
                </div>
            </form>

            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                                <td>
                                    @if($log->user)
                                        {{ $log->user->name }}
                                        <span class="text-muted text-xs">({{ $log->user->email }})</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-brand-100 text-brand-800 dark:bg-brand-900 dark:text-brand-200">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td>{{ $log->description ?? '—' }}</td>
                                <td class="whitespace-nowrap text-muted">{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-muted">No audit logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $logs->appends(request()->query())->links() }}
        </div>
    </x-page-container>
</x-app-layout>
