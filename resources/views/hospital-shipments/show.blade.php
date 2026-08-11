<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-section-label">Logistics</p>
            <h2 class="heading-page">{{ $transfer->transfer_number }}</h2>
        </div>
    </x-slot>

    <x-page-container>
        <x-module.back-link :href="getDashboardHospitalShipmentRoute('index')" label="Back to road deliveries" class="mb-6" />

        <x-module.detail-card title="Road delivery details">
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-module.detail-field label="Medicines" :value="$transfer->medicinesLabel()" />
                <x-module.detail-field label="Batches on delivery" :value="(string) $transfer->lineCount()" />
                <x-module.detail-field label="Total quantity sent" :value="number_format($transfer->quantity_sent)" />
                <x-module.detail-field label="Route" value="Lae AMS → Modilon Hospital (by road)" />
                <x-module.detail-field label="Assigned vehicle">
                    @if($transfer->vehicle)
                        <span class="font-medium text-ink dark:text-zinc-200">{{ $transfer->vehicle->displayLabel() }}</span>
                        <span class="block text-xs text-muted">{{ $transfer->vehicle->typeLabel() }} · ready for tracking</span>
                    @else
                        <span class="text-sm text-muted">Not recorded</span>
                    @endif
                </x-module.detail-field>
                <x-module.detail-field label="Dispatched" :value="$transfer->formatSentDate()" />
                <x-module.detail-field label="Estimated arrival">
                    @if($transfer->expected_arrival_at)
                        <span @class(['font-medium', 'text-rose-700 dark:text-rose-300' => $transfer->isArrivalOverdue()])>
                            {{ $transfer->formatExpectedArrival() }}
                            @if($transfer->isArrivalOverdue())
                                <span class="block text-xs font-semibold uppercase tracking-wide">Overdue</span>
                            @else
                                <span class="block text-xs text-muted">{{ $transfer->expected_arrival_at->diffForHumans() }}</span>
                            @endif
                        </span>
                    @else
                        <span class="text-sm text-muted">Not set</span>
                    @endif
                </x-module.detail-field>
                <x-module.detail-field label="Status">
                    <x-module.status-badge :variant="$transfer->status" :label="logisticsTransferStatusLabel($transfer->status)" />
                </x-module.detail-field>
                <x-module.detail-field label="Dispatched by" :value="$transfer->sender->name ?? 'N/A'" />
                @if($transfer->receiver)
                    <x-module.detail-field label="Received by" :value="$transfer->receiver->name . ' · ' . formatDate($transfer->received_at)" />
                @endif
            </dl>

            @php
                $lines = $transfer->items->isNotEmpty()
                    ? $transfer->items
                    : collect([(object) [
                        'drug' => $transfer->drug,
                        'batch_number' => $transfer->batch_number,
                        'quantity_sent' => $transfer->quantity_sent,
                        'destinationDrug' => $transfer->destinationDrug,
                    ]]);
            @endphp
            <div class="module-table-wrap mt-6 overflow-x-auto">
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Drug</th>
                            <th>Source batch</th>
                            <th>Qty</th>
                            <th>Modilon batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $line)
                            <tr>
                                <td>
                                    {{ $line->drug->drug_name ?? 'N/A' }}
                                    @if($line->drug)
                                        <span class="text-muted">({{ $line->drug->dosage }})</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">{{ $line->batch_number }}</td>
                                <td class="whitespace-nowrap">{{ number_format($line->quantity_sent) }}</td>
                                <td class="whitespace-nowrap">
                                    @if($line->destinationDrug ?? null)
                                        <a href="{{ getDashboardDrugRoute('show', $line->destinationDrug) }}" class="module-table-link">
                                            {{ $line->destinationDrug->batch_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">Pending receipt</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transfer->hospitalOrder)
                <a href="{{ getDashboardHospitalOrderRoute('show', $transfer->hospitalOrder) }}" class="module-table-link mt-4 inline-flex text-sm">
                    Linked order {{ $transfer->hospitalOrder->order_number }} →
                </a>
            @endif

            @if($transfer->notes)
                <p class="mt-4 text-sm text-ink-secondary dark:text-zinc-300">{{ $transfer->notes }}</p>
            @endif
        </x-module.detail-card>

        @if($transfer->isRoadLeg())
            <x-module.detail-card title="GPS tracking" class="mt-6">
                @if($transfer->isTrackable())
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            @if($transfer->vehicle->hasKnownLocation())
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $transfer->vehicle->isTrackingStale() ? 'bg-amber-500' : 'bg-health-600' }}"></span>
                                    <p class="font-medium text-ink">
                                        {{ $transfer->vehicle->isTrackingStale() ? 'Signal lost / paused' : 'Live — vehicle is sharing its location' }}
                                    </p>
                                </div>
                                <p class="mt-1 text-xs text-muted">
                                    Last ping {{ $transfer->vehicle->last_ping_at->diffForHumans() }}
                                    @if($transfer->vehicle->last_speed_kmh !== null)
                                        · {{ round($transfer->vehicle->last_speed_kmh) }} km/h
                                    @endif
                                </p>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-zinc-400"></span>
                                    <p class="font-medium text-ink">Not started yet</p>
                                </div>
                                <p class="mt-1 text-xs text-muted">Waiting for the driver to open the tracking link and tap Start Tracking.</p>
                            @endif
                        </div>

                        <a href="{{ getDashboardLiveMapRoute('index') }}" class="btn-module-secondary text-center">View on live map</a>
                    </div>

                    @if(auth()->user()->hasRole('store_manager'))
                        <div
                            x-data="{ copied: false, link: @js($transfer->driverTrackingUrl()) }"
                            class="mt-5 rounded-lg border border-line bg-surface-muted p-4"
                        >
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted">Driver tracking link</p>
                            <p class="mt-1 text-xs text-ink-secondary">Send this to the driver (SMS or WhatsApp). It opens a simple page on their phone with a Start Tracking button — no MediTrack login needed. Expires in 24 hours.</p>
                            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                                <input type="text" readonly x-ref="linkInput" :value="link" class="input-field flex-1 text-xs" onclick="this.select()">
                                <button
                                    type="button"
                                    class="btn-brand whitespace-nowrap text-xs uppercase tracking-widest"
                                    @click="navigator.clipboard.writeText(link); copied = true; setTimeout(() => copied = false, 2000)"
                                >
                                    <span x-show="!copied">Copy link</span>
                                    <span x-show="copied">Copied!</span>
                                </button>
                            </div>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-muted">Tracking is only active while this delivery is in transit.</p>
                @endif
            </x-module.detail-card>
        @endif
    </x-page-container>
</x-app-layout>
