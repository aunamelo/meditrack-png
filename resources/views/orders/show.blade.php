@php
    $order->loadMissing(['items.medicine', 'items.drug', 'medicine']);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Procurement</p>
            <h2 class="heading-page">Order {{ $order->order_number }}</h2>
        </div>
    </x-slot>

    <x-page-container x-data="{ showReceiveModal: {{ old('_receive_modal') && $errors->any() ? 'true' : 'false' }} }">
        <x-module.flash />

        <div class="module-actions-bar">
            <x-module.back-link :href="getDashboardOrderRoute('index')" label="Back to Orders" />
            <div class="flex flex-wrap gap-3">
                @if(canManageOrders() && $order->status === 'pending' && $order->created_by === auth()->id())
                    <a href="{{ getDashboardOrderRoute('edit', $order) }}" class="btn-module-secondary">Edit</a>
                    <details class="relative inline-block">
                        <summary class="inline-flex cursor-pointer list-none items-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-rose-700 hover:bg-rose-100">Cancel</summary>
                        <form action="{{ getDashboardOrderRoute('cancel', $order) }}" method="POST" class="absolute right-0 z-10 mt-2 w-80 rounded-xl border border-line bg-surface p-4 shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
                            @csrf
                            <label for="cancel_reason" class="form-label">Cancellation Reason</label>
                            <textarea name="reason" id="cancel_reason" rows="3" required class="input-field"></textarea>
                            <button type="submit" class="btn-brand mt-2 w-full text-xs uppercase">Confirm Cancel</button>
                        </form>
                    </details>
                @endif
                @if(canApproveOrders() && $order->canApprove())
                    <form action="{{ getDashboardOrderRoute('approve', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-white hover:bg-blue-700">Approve</button>
                    </form>
                @endif
                @if(canApproveOrders() && $order->status === 'pending')
                    <details class="relative inline-block">
                        <summary class="inline-flex cursor-pointer list-none items-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-rose-700 hover:bg-rose-100">Cancel</summary>
                        <form action="{{ getDashboardOrderRoute('cancel', $order) }}" method="POST" class="absolute right-0 z-10 mt-2 w-80 rounded-xl border border-line bg-surface p-4 shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
                            @csrf
                            <label for="admin_cancel_reason" class="form-label">Cancellation Reason</label>
                            <textarea name="reason" id="admin_cancel_reason" rows="3" required class="input-field"></textarea>
                            <button type="submit" class="btn-brand mt-2 w-full text-xs uppercase">Confirm Cancel</button>
                        </form>
                    </details>
                @endif
                @if(canApproveOrders() && $order->canReceive())
                    <button type="button" @click="showReceiveModal = true" class="btn-brand text-xs uppercase tracking-wider">Receive</button>
                @endif
                @if($order->canAdvancePipeline() && (canApproveOrders() || (canManageOrders() && $order->created_by === auth()->id())))
                    <details class="relative inline-block">
                        <summary class="btn-brand inline-flex cursor-pointer list-none text-xs uppercase tracking-wider">{{ $order->nextPipelineActionLabel() }}</summary>
                        <form action="{{ getDashboardOrderRoute('advance-pipeline', $order) }}" method="POST" class="absolute right-0 z-10 mt-2 w-80 rounded-xl border border-line bg-surface p-4 shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
                            @csrf
                            <p class="mb-3 text-sm text-muted">Move this order to the next import pipeline stage.</p>
                            <label for="pipeline_notes" class="form-label">Notes (optional)</label>
                            <textarea name="notes" id="pipeline_notes" rows="2" class="input-field" placeholder="Shipment ref, customs entry, FX approval..."></textarea>
                            <button type="submit" class="btn-brand mt-3 w-full text-xs uppercase">Confirm</button>
                        </form>
                    </details>
                @endif
            </div>
        </div>

        @if(! in_array($order->status, ['cancelled'], true))
            <div class="module-panel mb-6 p-6">
                <x-order-pipeline :order="$order" />
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-module.detail-card title="Order Information">
                <p class="font-display text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $order->order_number }}</p>
                <div class="mt-3">
                    <x-module.status-badge :variant="$order->status" :label="$order->statusLabel()" />
                </div>
                <dl class="mt-4 space-y-4">
                    <x-module.detail-field label="Order Date" :value="$order->formatOrderDate()" />
                    <x-module.detail-field label="Created By" :value="$order->creator->name ?? 'N/A'" />
                    <x-module.detail-field label="Approval Status">
                        @if($order->approved_at)
                            Approved on {{ $order->approved_at->format('M d, Y') }} by {{ $order->approver->name ?? 'N/A' }}
                        @else
                            Pending approval
                        @endif
                    </x-module.detail-field>
                    @if($order->supplier_invoice)
                        <x-module.detail-field label="Invoice #" :value="$order->supplier_invoice" />
                    @endif
                    @if($order->invoice_amount)
                        <x-module.detail-field label="Invoice Amount" :value="'K ' . number_format($order->invoice_amount, 2)" />
                    @endif
                </dl>
            </x-module.detail-card>

            <x-module.detail-card title="Supplier & Delivery">
                <dl class="space-y-4">
                    <x-module.detail-field label="Supplier" :value="$order->supplier" />
                    <x-module.detail-field label="Source" :value="ucfirst($order->source)" />
                    <x-module.detail-field label="Expected Delivery" :value="$order->formatDeliveryDate()" />
                    @if($order->isOverdue())
                        <x-module.detail-field label="Overdue">
                            <span class="text-rose-600">{{ $order->daysOverdue() }} days</span>
                        </x-module.detail-field>
                    @elseif($order->expected_delivery_date && !in_array($order->status, ['received', 'cancelled']))
                        <x-module.detail-field label="Days to Delivery" :value="now()->diffInDays($order->expected_delivery_date, false) . ' days'" />
                    @endif
                </dl>
            </x-module.detail-card>

            <x-module.detail-card title="Receipt">
                <p class="font-display text-3xl font-bold text-ink dark:text-zinc-100">{{ number_format($order->quantity_received ?? 0) }}</p>
                <p class="mt-1 text-sm text-muted">of {{ number_format($order->quantity_ordered) }} received</p>
                <div class="module-progress-track mt-4 w-full">
                    <div class="module-progress-bar" style="width: {{ $order->getProgressPercentage() }}%"></div>
                </div>
                <dl class="mt-4 space-y-4">
                    <x-module.detail-field label="Receipt Status">
                        @if($order->status === 'received')
                            Complete
                        @elseif($order->status === 'partial')
                            Partial delivery
                        @else
                            Not yet received
                        @endif
                    </x-module.detail-field>
                    @if($order->actual_delivery_date)
                        <x-module.detail-field label="Received Date" :value="$order->actual_delivery_date->format('M d, Y')" />
                    @endif
                    @if($order->receiver)
                        <x-module.detail-field label="Received By" :value="$order->receiver->name" />
                    @endif
                </dl>
            </x-module.detail-card>
        </div>

        <x-module.detail-card title="Order Lines" class="mt-6">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-muted">{{ max($order->items->count(), 1) }} {{ Str::plural('item', max($order->items->count(), 1)) }} · {{ number_format($order->quantity_ordered) }} units total</p>
            </div>
            <div class="module-table-wrap overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th class="text-right">Ordered</th>
                            <th class="text-right">Received</th>
                            <th class="text-right">Remaining</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->items as $item)
                            <tr>
                                <td>
                                    @if($item->medicine && auth()->user()->hasAnyRole(['admin', 'procurement_officer']))
                                        <a href="{{ getDashboardMedicineRoute('show', $item->medicine) }}" class="module-table-link">{{ $item->medicine->name }}</a>
                                        <span class="block text-xs text-muted">{{ $item->medicine->dosage }}</span>
                                    @elseif($item->medicine)
                                        <span class="font-medium">{{ $item->medicine->name }}</span>
                                        <span class="block text-xs text-muted">{{ $item->medicine->dosage }}</span>
                                    @elseif($item->drug)
                                        <a href="{{ getDashboardDrugRoute('show', $item->drug) }}" class="module-table-link">{{ $item->drug->drug_name }}</a>
                                        <span class="block text-xs text-muted">{{ $item->drug->dosage }}</span>
                                    @else
                                        N/A
                                    @endif
                                    @if($item->drug && $item->medicine)
                                        <span class="mt-1 block text-xs text-muted">Batch: <a href="{{ getDashboardDrugRoute('show', $item->drug) }}" class="module-table-link">{{ $item->drug->batch_number }}</a></span>
                                    @endif
                                </td>
                                <td class="text-right font-medium">{{ number_format($item->quantity_ordered) }}</td>
                                <td class="text-right font-medium">{{ number_format($item->quantity_received ?? 0) }}</td>
                                <td class="text-right font-medium">{{ number_format($item->remainingQuantity()) }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="module-progress-track w-20">
                                            <div class="module-progress-bar" style="width: {{ $item->getProgressPercentage() }}%"></div>
                                        </div>
                                        <span class="text-xs text-muted">{{ $item->getProgressPercentage() }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>
                                    @if($order->medicine)
                                        <span class="font-medium">{{ $order->medicine->name }}</span>
                                        <span class="block text-xs text-muted">{{ $order->medicine->dosage }}</span>
                                    @elseif($order->drug)
                                        <a href="{{ getDashboardDrugRoute('show', $order->drug) }}" class="module-table-link">{{ $order->drug->drug_name }}</a>
                                        <span class="block text-xs text-muted">{{ $order->drug->dosage }}</span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="text-right font-medium">{{ number_format($order->quantity_ordered) }}</td>
                                <td class="text-right font-medium">{{ number_format($order->quantity_received ?? 0) }}</td>
                                <td class="text-right font-medium">{{ number_format(max(0, $order->quantity_ordered - ($order->quantity_received ?? 0))) }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="module-progress-track w-20">
                                            <div class="module-progress-bar" style="width: {{ $order->getProgressPercentage() }}%"></div>
                                        </div>
                                        <span class="text-xs text-muted">{{ $order->getProgressPercentage() }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-module.detail-card>

        @if($order->notes)
            <x-module.detail-card title="Notes" class="mt-6">
                <p class="whitespace-pre-line text-sm text-ink-secondary dark:text-zinc-300">{{ $order->notes }}</p>
            </x-module.detail-card>
        @endif

        @if(canApproveOrders())
            @include('orders.receive-modal', ['order' => $order])
        @endif
    </x-page-container>
</x-app-layout>
