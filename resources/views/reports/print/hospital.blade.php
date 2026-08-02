@extends('layouts.print')

@section('title', $report['title'].' · MediTrack PNG')

@section('content')
    <p class="brand">MediTrack PNG</p>
    <h1>{{ $report['title'] }}</h1>
    <p class="meta">
        {{ $report['facility'] }} · {{ $report['period']['from'] }} — {{ $report['period']['to'] }}
    </p>

    <div class="grid">
        @foreach([
            ['Stock batches (now)', $report['inventory']['total_batches']],
            ['Units on hand (now)', number_format($report['inventory']['total_units'])],
            ['Low stock', $report['inventory']['low_stock_batches']],
            ['Expiring soon', $report['inventory']['expiring_soon']],
            ['Requests filed', $report['requests']['total']],
            ['Requests pending', $report['requests']['pending']],
            ['Deliveries received', $report['deliveries']['received'].' / '.$report['deliveries']['total']],
            ['Units dispensed', number_format($report['dispensing']['units_dispensed'])],
            ['Open discrepancies', $report['discrepancies']['open']],
            ['Stock takes', $report['stock_takes']['total']],
            ['Stock take net Δ', number_format($report['stock_takes']['net_delta'])],
            ['Units requested', number_format($report['requests']['units_requested'])],
        ] as [$label, $value])
            <div class="card">
                <div class="label">{{ $label }}</div>
                <div class="value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <h2>Recent Lae AMS requests</h2>
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Medicine</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Filed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['recent_requests'] as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->drug_name }} {{ $order->dosage }}</td>
                    <td>{{ number_format($order->quantity_requested) }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ $order->created_at?->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No requests in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Recent dispensing</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Medicine</th>
                <th>Patient</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['recent_dispensing'] as $record)
                <tr>
                    <td>{{ $record->dispensed_at?->format('M d, Y') }}</td>
                    <td>{{ $record->drug->drug_name ?? '—' }}</td>
                    <td>{{ $record->patient?->full_name ?: '—' }}</td>
                    <td>{{ number_format($record->quantity_dispensed) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No dispensing records in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
