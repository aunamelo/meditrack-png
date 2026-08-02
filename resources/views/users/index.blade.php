<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Administration</p>
            <h2 class="heading-page">User Management</h2>
        </div>
    </x-slot>

    <x-page-container>
        <div class="surface-panel">
            <div class="p-6">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-zinc-100">
                            @if(auth()->user()->hasRole('admin'))
                                Portal staff accounts
                            @else
                                Pharmacist accounts
                            @endif
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                            @if(auth()->user()->hasRole('admin'))
                                Create, edit, or deactivate Procurement Officers, Store Managers, and Pharmacy Managers.
                            @else
                                Create, edit, or deactivate Pharmacist accounts for Modilon Hospital.
                            @endif
                        </p>
                    </div>
                    <a href="{{ getDashboardUserRoute('create') }}" class="mt-4 inline-flex items-center rounded-md border border-transparent bg-brand-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-brand-700 sm:mt-0">
                        Add user
                    </a>
                </div>

                @if(session('success'))
                    <div class="mb-6 rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-950/40 dark:text-green-300">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ getDashboardUserRoute('index') }}" method="GET" class="mb-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <label for="search" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name or email..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                        </div>
                        @if(count($roleOptions) > 1)
                            <div>
                                <label for="role" class="mb-1 block text-sm font-medium text-gray-700 dark:text-zinc-300">Role</label>
                                <select name="role" id="role" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-600 focus:ring focus:ring-brand-600 focus:ring-opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                    <option value="">All roles</option>
                                    @foreach($roleOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button type="submit" class="inline-flex items-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Filter</button>
                        @if(request()->hasAny(['search', 'role']))
                            <a href="{{ getDashboardUserRoute('index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">Clear</a>
                        @endif
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                        <thead class="bg-gray-50 dark:bg-zinc-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">Role</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-transparent">
                            @forelse($users as $managedUser)
                                @php
                                    $roleKey = $managedUser->getRoleNames()->first();
                                    $roleLabel = config("portal.roles.{$roleKey}.label", ucfirst(str_replace('_', ' ', $roleKey ?? '')));
                                    $isDeactivated = $managedUser->trashed();
                                @endphp
                                <tr class="{{ $isDeactivated ? 'opacity-60' : '' }}">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 dark:text-zinc-100">
                                        <span class="inline-flex items-center gap-2">
                                            {{ $managedUser->name }}
                                            @if($isDeactivated)
                                                <span class="rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-zinc-700 dark:text-zinc-300">Deactivated</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-zinc-400">{{ $managedUser->email }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-zinc-400">{{ $roleLabel }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                        @if($isDeactivated)
                                            <form
                                                action="{{ getDashboardUserRoute('restore', $managedUser) }}"
                                                method="POST"
                                                class="inline"
                                                data-confirm="Reactivate this user account? They will be able to sign in again."
                                                data-confirm-title="Reactivate user"
                                                data-confirm-label="Reactivate"
                                            >
                                                @csrf
                                                <button type="submit" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">Reactivate</button>
                                            </form>
                                        @else
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ getDashboardUserRoute('edit', $managedUser) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">Edit</a>
                                                <form
                                                    action="{{ getDashboardUserRoute('destroy', $managedUser) }}"
                                                    method="POST"
                                                    class="inline"
                                                    data-confirm="Deactivate this user account? They will lose portal access. Audit history is kept."
                                                    data-confirm-title="Deactivate user"
                                                    data-confirm-label="Deactivate"
                                                    data-confirm-danger="1"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-semibold text-red-600 hover:text-red-700 dark:text-red-400">Deactivate</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-zinc-400">
                                        No user accounts found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="mt-6">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-page-container>
</x-app-layout>
