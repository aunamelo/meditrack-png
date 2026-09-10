<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Administration</p>
            <h2 class="heading-page">Audit Logs</h2>
        </div>
    </x-slot>

    <x-page-container>
        <div class="surface-panel">
            <div class="p-6">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-zinc-100">System Activity Log</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Track all user actions and system events for security and compliance.</p>
                    </div>
                    <a href="{{ route('admin.dashboard.audit-logs.export', request()->query()) }}" class="mt-4 inline-flex items-center rounded-md border border-transparent bg-brand-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-brand-700 sm:mt-0">
                        Export CSV
                    </a>
                </div>

                {{-- Summary Cards --}}
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-zinc-400">Total Events</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-zinc-100">{{ number_format($summary['total_events']) }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-zinc-400">Failed Logins</p>
                                <p class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400">{{ number_format($summary['failed_logins']) }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-zinc-400">Unique Users</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-zinc-100">{{ number_format($summary['unique_users']) }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-zinc-400">Critical Events</p>
                                <p class="mt-1 text-2xl font-semibold text-purple-600 dark:text-purple-400">{{ number_format($summary['critical_events']) }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.dashboard.audit-logs.index') }}" method="GET" class="mb-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label for="search" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">Global Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search all fields..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label for="user_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">User</label>
                            <select name="user_id" id="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                <option value="">All Users</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="severity" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">Severity</label>
                            <select name="severity" id="severity" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                <option value="">All Severities</option>
                                <option value="INFO" {{ request('severity') == 'INFO' ? 'selected' : '' }}>INFO</option>
                                <option value="WARNING" {{ request('severity') == 'WARNING' ? 'selected' : '' }}>WARNING</option>
                                <option value="CRITICAL" {{ request('severity') == 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
                            </select>
                        </div>
                        <div>
                            <label for="action" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">Action</label>
                            <input type="text" name="action" id="action" value="{{ request('action') }}" placeholder="e.g., login, create"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label for="date_from" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">From Date</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label for="date_to" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">To Date</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button type="submit" class="inline-flex items-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Filter</button>
                        @if(request()->hasAny(['search', 'user_id', 'action', 'severity', 'date_from', 'date_to']))
                            <a href="{{ route('admin.dashboard.audit-logs.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">Clear</a>
                        @endif
                        <button type="button" onclick="document.getElementById('deleteOldForm').classList.toggle('hidden')" class="ml-auto inline-flex items-center rounded-md border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 dark:border-red-900 dark:bg-red-950 dark:text-red-400">Delete Old Logs</button>
                    </div>

                    {{-- Delete Old Logs Form --}}
                    <div id="deleteOldForm" class="hidden mt-4 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/40">
                        <form action="{{ route('admin.dashboard.audit-logs.destroy-old') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete logs older than the specified number of days? This action cannot be undone.');">
                            @csrf
                            <div class="flex flex-wrap items-center gap-4">
                                <label class="text-sm font-medium text-red-800 dark:text-red-300">Delete logs older than</label>
                                <input type="number" name="days" value="90" min="1" class="w-20 rounded-md border-red-300 bg-white shadow-sm focus:border-red-600 focus:ring focus:ring-red-600 focus:ring-opacity-50 dark:border-red-900 dark:bg-zinc-900 dark:text-zinc-100">
                                <span class="text-sm text-red-800 dark:text-red-300">days</span>
                                <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Delete</button>
                                <button type="button" onclick="document.getElementById('deleteOldForm').classList.add('hidden')" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400">Cancel</button>
                            </div>
                        </form>
                    </div>
                </form>

                <div class="mb-4 flex items-center gap-4">
                    <span class="text-sm text-gray-600 dark:text-zinc-400">Per page:</span>
                    <select name="per_page" onchange="window.location.href='{{ route('admin.dashboard.audit-logs.index', array_merge(request()->query(), ['per_page' => ''])) }}' + this.value" class="rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="text-sm text-gray-600 dark:text-zinc-400">Showing {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                        <thead class="bg-gray-50 dark:bg-zinc-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">
                                    <a href="{{ route('admin.dashboard.audit-logs.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'desc' ? 'asc' : 'desc'])) }}" class="hover:text-brand-600">
                                        Date/Time {{ request('sort') == 'created_at' ? (request('direction') == 'asc' ? '↑' : '↓') : '' }}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">User</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">
                                    <a href="{{ route('admin.dashboard.audit-logs.index', array_merge(request()->query(), ['sort' => 'action', 'direction' => request('sort') == 'action' && request('direction') == 'desc' ? 'asc' : 'desc'])) }}" class="hover:text-brand-600">
                                        Action {{ request('sort') == 'action' ? (request('direction') == 'asc' ? '↑' : '↓') : '' }}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">Severity</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">
                                    <a href="{{ route('admin.dashboard.audit-logs.index', array_merge(request()->query(), ['sort' => 'ip_address', 'direction' => request('sort') == 'ip_address' && request('direction') == 'desc' ? 'asc' : 'desc'])) }}" class="hover:text-brand-600">
                                        IP Address {{ request('sort') == 'ip_address' ? (request('direction') == 'asc' ? '↑' : '↓') : '' }}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-transparent">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-900/50">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-zinc-100">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-zinc-400">
                                        @if($log->user)
                                            <div>{{ $log->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $log->user->email }}</div>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-brand-100 text-brand-800 dark:bg-brand-900 dark:text-brand-200">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($log->severity === 'CRITICAL')
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">CRITICAL</span>
                                        @elseif($log->severity === 'WARNING')
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">WARNING</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-zinc-800 dark:text-zinc-300">INFO</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-zinc-400">{{ $log->description ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-mono text-gray-500 dark:text-zinc-500">{{ $log->ip_address ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                        <button type="button" onclick="viewDetails({{ $log->id }})" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-zinc-400">
                                        No audit logs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="mt-6">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-page-container>

    {{-- Detail Modal --}}
    <div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-zinc-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-zinc-100">Audit Log Details</h3>
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:text-zinc-400 dark:hover:text-zinc-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="modalContent" class="space-y-4">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewDetails(id) {
            fetch('{{ route('admin.dashboard.audit-logs.show', ':id') }}'.replace(':id', id))
                .then(response => response.json())
                .then(data => {
                    const log = data.log;
                    const related = data.related_events;

                    let html = `
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Date/Time</label>
                                <div class="text-sm text-gray-900 dark:text-zinc-100">${new Date(log.created_at).toLocaleString()}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Event ID</label>
                                <div class="text-sm font-mono text-gray-900 dark:text-zinc-100">${log.event_id || '—'}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">User</label>
                                <div class="text-sm text-gray-900 dark:text-zinc-100">${log.user ? log.user.name : 'N/A'}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Email</label>
                                <div class="text-sm text-gray-900 dark:text-zinc-100">${log.user ? log.user.email : 'N/A'}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Action</label>
                                <div class="text-sm font-medium text-gray-900 dark:text-zinc-100">${log.action}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Severity</label>
                                <div class="text-sm text-gray-900 dark:text-zinc-100">${log.severity}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">IP Address</label>
                                <div class="text-sm font-mono text-gray-900 dark:text-zinc-100">${log.ip_address || '—'}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Session ID</label>
                                <div class="text-sm font-mono text-xs text-gray-900 dark:text-zinc-100">${log.session_id || '—'}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Request Method</label>
                                <div class="text-sm text-gray-900 dark:text-zinc-100">${log.request_method || '—'}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Request Path</label>
                                <div class="text-sm text-gray-900 dark:text-zinc-100">${log.request_path || '—'}</div>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Description</label>
                            <div class="mt-1 text-sm text-gray-900 dark:text-zinc-100">${log.description || '—'}</div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">User Agent</label>
                            <div class="mt-1 rounded bg-gray-100 p-2 text-xs break-all text-gray-900 dark:bg-zinc-800 dark:text-zinc-100">${log.user_agent || '—'}</div>
                        </div>
                    `;

                    if (log.old_values && Object.keys(log.old_values).length > 0) {
                        html += `
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Old Values</label>
                                <pre class="mt-1 overflow-auto rounded bg-gray-100 p-2 text-xs text-gray-900 dark:bg-zinc-800 dark:text-zinc-100">${JSON.stringify(log.old_values, null, 2)}</pre>
                            </div>
                        `;
                    }

                    if (log.new_values && Object.keys(log.new_values).length > 0) {
                        html += `
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">New Values</label>
                                <pre class="mt-1 overflow-auto rounded bg-gray-100 p-2 text-xs text-gray-900 dark:bg-zinc-800 dark:text-zinc-100">${JSON.stringify(log.new_values, null, 2)}</pre>
                            </div>
                        `;
                    }

                    if (related && related.length > 0) {
                        html += `
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-zinc-400">Related Events (same user within 5 minutes)</label>
                                <div class="mt-2 space-y-2">
                        `;
                        related.forEach(rel => {
                            html += `
                                <div class="rounded bg-gray-50 p-2 text-xs text-gray-900 dark:bg-zinc-800 dark:text-zinc-100">
                                    <span class="font-medium">${rel.action}</span> - ${new Date(rel.created_at).toLocaleString()}
                                </div>
                            `;
                        });
                        html += `</div></div>`;
                    }

                    html += `
                        <div class="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-zinc-700">
                            <button type="button" onclick="deleteLog(${log.id})" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Delete This Log</button>
                        </div>
                    `;

                    document.getElementById('modalContent').innerHTML = html;
                    document.getElementById('detailModal').classList.remove('hidden');
                });
        }

        function closeModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        function deleteLog(id) {
            if (!confirm('Are you sure you want to delete this audit log? This action cannot be undone.')) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('{{ route('admin.dashboard.audit-logs.destroy', ':id') }}'.replace(':id', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    window.location.href = '{{ route('admin.dashboard.audit-logs.index') }}';
                } else {
                    alert('Failed to delete audit log: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete audit log: ' + error.message);
            });
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Close modal on background click
        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</x-app-layout>
