<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Administration</p>
            <h2 class="heading-page">Create User</h2>
        </div>
    </x-slot>

    <x-page-container>
        <div class="surface-panel">
            <div class="p-6">
                <nav class="mb-6 flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ getDashboardUserRoute('index') }}" class="text-sm font-medium text-gray-700 hover:text-brand-600 dark:text-zinc-300">Users</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-zinc-400">Create</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                @include('users.partials.form', ['roleOptions' => $roleOptions])
            </div>
        </div>
    </x-page-container>
</x-app-layout>
