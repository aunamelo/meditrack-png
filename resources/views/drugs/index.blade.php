<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Inventory</p>
            <h2 class="heading-page">Drug Inventory</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.flash />

        <x-module.hero
            icon="cube"
            :description="auth()->user()->hasAnyRole(['pharmacy_manager', 'pharmacist'])
                ? 'Modilon Hospital pharmacy stock — view batch, quantity, and expiry before dispensing.'
                : (auth()->user()->hasRole('store_manager')
                    ? 'Lae AMS warehouse stock batches.'
                    : 'NDoH stock batches — created when procurement orders are received')"
            :action-url="auth()->user()->hasRole('admin') ? getDashboardDrugRoute('create') : null"
            :action-label="auth()->user()->hasRole('admin') ? 'Manual entry' : null"
        />

        <div class="module-panel p-6">
            <form action="{{ getDashboardDrugRoute('index') }}" method="GET" class="module-filter mb-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label for="search" class="form-label">Search</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Drug name or batch number..." class="input-field pl-10">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="input-field">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expiring_soon" {{ request('status') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            @if(auth()->user()->hasRole('admin'))
                                <option value="written_off" {{ request('status') == 'written_off' ? 'selected' : '' }}>Written Off</option>
                            @endif
                        </select>
                    </div>
                    @if(auth()->user()->hasRole('admin'))
                        <div>
                            <label for="level" class="form-label">Level</label>
                            <select name="level" id="level" class="input-field">
                                <option value="">All Levels</option>
                                <option value="ndoh" {{ request('level') == 'ndoh' ? 'selected' : '' }}>NDoH</option>
                                <option value="lae_ams" {{ request('level') == 'lae_ams' ? 'selected' : '' }}>Lae AMS</option>
                                <option value="modilon_hospital" {{ request('level') == 'modilon_hospital' ? 'selected' : '' }}>Modilon Hospital</option>
                            </select>
                        </div>
                    @endif
                </div>
                <div class="mt-4 flex justify-end gap-3">
                    <button type="submit" class="btn-brand text-xs uppercase tracking-wider">Search</button>
                    <a href="{{ getDashboardDrugRoute('index') }}" class="btn-module-secondary">Clear</a>
                </div>
            </form>

            @if($drugs->count() > 0)
                <div class="module-table-wrap overflow-x-auto">
                    <table class="module-table">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <a href="{{ getDashboardDrugRoute('index', array_merge(request()->except('sort', 'direction'), ['sort' => 'drug_name', 'direction' => request('sort') == 'drug_name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-brand-600">
                                        Drug Name @if(request('sort') == 'drug_name')<span>{{ request('direction') == 'asc' ? '↑' : '↓' }}</span>@endif
                                    </a>
                                </th>
                                <th scope="col">Batch #</th>
                                <th scope="col">
                                    <a href="{{ getDashboardDrugRoute('index', array_merge(request()->except('sort', 'direction'), ['sort' => 'expiry_date', 'direction' => request('sort') == 'expiry_date' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-brand-600">
                                        Expiry @if(request('sort') == 'expiry_date')<span>{{ request('direction') == 'asc' ? '↑' : '↓' }}</span>@endif
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{{ getDashboardDrugRoute('index', array_merge(request()->except('sort', 'direction'), ['sort' => 'quantity_on_hand', 'direction' => request('sort') == 'quantity_on_hand' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-brand-600">
                                        Qty On Hand @if(request('sort') == 'quantity_on_hand')<span>{{ request('direction') == 'asc' ? '↑' : '↓' }}</span>@endif
                                    </a>
                                </th>
                                <th scope="col">Reorder Pt</th>
                                <th scope="col">Status</th>
                                <th scope="col">Days to Expiry</th>
                                <th scope="col" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($drugs as $drug)
                                <tr>
                                    <td class="whitespace-nowrap">
                                        <div class="font-semibold text-ink dark:text-zinc-100">{{ $drug->drug_name }}</div>
                                        <div class="text-xs text-muted">{{ $drug->dosage }} {{ $drug->unit }}</div>
                                    </td>
                                    <td class="whitespace-nowrap">{{ $drug->batch_number }}</td>
                                    <td class="whitespace-nowrap">{{ $drug->formatExpiry() }}</td>
                                    <td class="whitespace-nowrap font-semibold text-ink dark:text-zinc-100">{{ $drug->quantity_on_hand }}</td>
                                    <td class="whitespace-nowrap">{{ $drug->reorder_point }}</td>
                                    <td class="whitespace-nowrap">
                                        <x-module.status-badge :variant="$drug->status_badge" :label="match($drug->status_badge) {
                                            'active' => 'Active',
                                            'expiring_soon' => 'Expiring Soon',
                                            'expired' => 'Expired',
                                            'low_stock' => 'Low Stock',
                                            default => ucfirst($drug->status),
                                        }" />
                                    </td>
                                    <td class="whitespace-nowrap">
                                        @if($drug->days_until_expiry < 0)
                                            <span class="font-medium text-rose-600">{{ $drug->days_until_expiry }} days</span>
                                        @elseif($drug->days_until_expiry <= 180)
                                            <span class="font-medium text-amber-600">{{ $drug->days_until_expiry }} days</span>
                                        @else
                                            <span class="text-emerald-600">{{ $drug->days_until_expiry }} days</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-right">
                                        <div class="module-table-actions">
                                            <a href="{{ getDashboardDrugRoute('show', $drug->id) }}" class="module-table-action">View</a>
                                            @if(auth()->user()->hasRole('procurement_officer') && $drug->level == 'ndoh')
                                                <a href="{{ getDashboardDrugRoute('edit', $drug->id) }}" class="module-table-action module-table-action-edit">Edit</a>
                                            @endif
                                            @if(auth()->user()->hasRole('admin'))
                                                <form action="{{ getDashboardDrugRoute('destroy', $drug->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this drug?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-700">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $drugs->appends(request()->except('page'))->links() }}</div>
            @else
                <div class="module-empty py-12">
                    <div class="module-empty-icon">
                        <x-dashboard.icon name="cube" class="h-6 w-6 text-muted" />
                    </div>
                    <p class="text-sm font-semibold text-ink dark:text-zinc-200">No drugs found</p>
                    <p class="mt-1 text-sm text-muted">Inventory batches appear here after procurement orders are received.</p>
                    @if(auth()->user()->hasAnyRole(['admin', 'procurement_officer']))
                        <a href="{{ auth()->user()->hasRole('admin') ? getDashboardDrugRoute('create') : getDashboardMedicineRoute('index') }}" class="btn-brand mt-4 text-xs uppercase tracking-wider">
                            {{ auth()->user()->hasRole('admin') ? 'Manual entry' : 'Medicine catalog' }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </x-page-container>
</x-app-layout>
